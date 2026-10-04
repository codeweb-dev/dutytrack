# Simple Chat Migration

## Goal

Replace the current Reverb/WebSocket chat with a low-cost, request-based chat.

The finished chat must still support:

- viewing conversations and messages;
- sending messages;
- editing your own active messages;
- deleting your own messages;
- unread counts and read timestamps;
- the existing conversation access rules for students, coordinators, professors, and supervisors.

It must not use Reverb, Laravel Echo, Pusher, typing indicators, WebSocket listeners, or automatic polling.

## Current implementation

The message page currently has two update systems:

1. Reverb broadcasts message and typing events through Laravel Echo.
2. `wire:poll.visible.1500ms` checks the database every 1.5 seconds as a fallback.

Removing Reverb alone would not remove the recurring cost. A visible chat tab would still make about 40 polling requests per minute. The simple version should remove both systems.

## Target behavior

- A message is saved through the existing Livewire `sendMessage()` action and appears immediately for its sender.
- The other participant sees new or changed messages after reopening the conversation, reloading the page, or pressing a **Refresh** button.
- Editing remains available only to the sender and only before deletion.
- Deleting a message keeps its database row but removes its content.
- Both participants see **This message was deleted.** in the original timeline position.
- A deleted message cannot be edited or deleted again.
- `deleted_at` records when deletion happened as proof that the message record was deleted.

## Deletion and privacy rule

Add Laravel soft deletes to `chat_messages`, but do not retain the original message body:

1. Replace `body` with an empty string.
2. Soft-delete the row so Laravel sets `deleted_at`.
3. Keep the row ID, conversation ID, sender ID, created time, edited time, and deletion time.
4. Render the placeholder from the Blade view when `deleted_at` is present; do not store the placeholder as message content.

This preserves proof that a message existed and was deleted without retaining its private text. A content hash or message recovery system is intentionally not included.

## Required changes

### 1. Remove Reverb and broadcasting

- Remove `laravel/reverb` from `composer.json` and update `composer.lock` with `composer remove laravel/reverb`.
- Remove `laravel-echo` and `pusher-js` from `package.json` and update `package-lock.json` with `npm uninstall laravel-echo pusher-js`.
- Delete `config/reverb.php`.
- Delete `resources/js/echo.js` and its import from `resources/js/app.js`.
- Delete `app/Events/ConversationMessageSent.php`.
- Delete `app/Events/ConversationTypingStarted.php`.
- Remove the message and typing `broadcast(...)` calls and their event imports.
- Remove channel routing from `bootstrap/app.php` and delete `routes/channels.php`, because broadcasting is not used elsewhere in this project.
- Remove the Reverb connection from `config/broadcasting.php`, or remove the application override if broadcasting is no longer configured.
- Remove all `REVERB_*` and `VITE_REVERB_*` variables from local and deployed environments. Set `BROADCAST_CONNECTION=null` if the variable remains.

### 2. Remove listeners, polling, and typing indicators

In `resources/views/pages/⚡messages.blade.php`:

- Remove the `#[On(...)]` realtime listener methods and the `On` import.
- Remove `handleBroadcastedMessage()` and the payload-handling code.
- Remove the Echo subscription and cleanup Alpine code.
- Remove `wire:poll.visible.1500ms`.
- Remove `latestMessageId` and `latestMessageIdFor()` because they only detect background updates.
- Keep `refreshMessages()`, but simplify it to authorize the selected conversation, mark it read, clear the relevant computed properties, and dispatch the existing scroll event.
- Add a **Refresh** button that calls `refreshMessages()`.
- Remove `sendTyping()`, `typingUsers()`, typing-indicator cleanup, the input listener, and the typing status UI.

Also remove the now-unused typing feature:

- Delete `app/Models/ChatTypingIndicator.php`.
- Remove `typingIndicators()` from `ChatConversation`.
- Add a migration that drops `chat_typing_indicators`. Do not edit an already-deployed migration.

### 3. Add soft deletion

- Add a migration with `$table->softDeletes()` on `chat_messages`.
- Add Laravel's `SoftDeletes` trait to `ChatMessage`.
- Add `deleted_at` to the model's datetime casts.
- Change `deleteMessage()` to blank the body before calling `delete()`.
- Keep the current sender and conversation authorization checks.
- Load trashed messages in the chat timeline with `withTrashed()` so their placeholders stay in order.
- Make `ChatConversation::latestMessage()` include trashed messages so a deleted last message does not make the conversation look empty or reorder it incorrectly.
- Render **This message was deleted.** for deleted rows in the conversation preview and timeline.
- Hide edit/delete controls and the `edited` label for deleted rows.
- Keep deleted rows out of unread counts and `markConversationRead()` by leaving those normal queries unchanged.

## Tests

Update `tests/Feature/MessagingTest.php` to remove event and typing-indicator assertions, then verify:

- each authorized role can still send messages;
- a sender can still edit their own active message;
- another participant cannot edit or delete that message;
- deleting sets `deleted_at` instead of removing the row;
- deleting clears the stored body;
- both participants see **This message was deleted.**;
- deleted messages have no edit or delete controls;
- deleted messages cannot be edited through a direct Livewire call;
- a deleted latest message remains the conversation preview and is shown as deleted;
- manual refresh loads new messages and marks them read.

Run:

```bash
php artisan test --filter=MessagingTest
npm run build
```

## Acceptance criteria

- No Reverb process or WebSocket service is required.
- No browser WebSocket connection is opened.
- No automatic chat polling occurs.
- No typing-indicator database writes occur.
- Sending, manual refresh, editing, soft deletion, authorization, unread counts, and read timestamps work.
- Deleted content is absent from `chat_messages.body`, while `deleted_at` and message metadata remain as deletion proof.

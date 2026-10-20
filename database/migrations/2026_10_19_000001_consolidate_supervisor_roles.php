<?php

use App\Models\SupervisorProfile;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            foreach (Role::query()->whereIn('name', ['professor', 'company_supervisor'])->get() as $legacyRole) {
                $supervisor = Role::findOrCreate('supervisor', $legacyRole->guard_name);

                foreach (DB::table('model_has_roles')->where('role_id', $legacyRole->id)->get() as $membership) {
                    DB::table('model_has_roles')->insertOrIgnore([
                        'role_id' => $supervisor->id,
                        'model_type' => $membership->model_type,
                        'model_id' => $membership->model_id,
                    ]);

                    if ($legacyRole->name === 'professor' && $membership->model_type === (new \App\Models\User)->getMorphClass()) {
                        SupervisorProfile::query()->firstOrCreate(['user_id' => $membership->model_id], [
                            'supervisor_id' => 'LEGACY-PROF-'.$membership->model_id,
                            'company_name' => '',
                        ]);
                    }
                }

                foreach (DB::table('role_has_permissions')->where('role_id', $legacyRole->id)->get() as $permission) {
                    DB::table('role_has_permissions')->insertOrIgnore([
                        'role_id' => $supervisor->id,
                        'permission_id' => $permission->permission_id,
                    ]);
                }

                $legacyRole->delete();
            }

            DB::table('ojt_assignments')->whereNull('company_supervisor_id')->whereNotNull('professor_id')
                ->update(['company_supervisor_id' => DB::raw('professor_id')]);
            DB::table('evaluations')->where('evaluator_type', 'professor')->update(['evaluator_type' => 'company_supervisor']);

            foreach (DB::table('chat_conversations')->where('participant_role', 'professor')->get() as $conversation) {
                $existing = DB::table('chat_conversations')
                    ->where('ojt_assignment_id', $conversation->ojt_assignment_id)
                    ->where('participant_id', $conversation->participant_id)
                    ->where('participant_role', 'supervisor')->first();

                if ($existing) {
                    DB::table('chat_messages')->where('chat_conversation_id', $conversation->id)
                        ->update(['chat_conversation_id' => $existing->id]);
                    DB::table('chat_conversations')->where('id', $conversation->id)->delete();
                } else {
                    DB::table('chat_conversations')->where('id', $conversation->id)->update(['participant_role' => 'supervisor']);
                }
            }
        });

        Schema::rename('professor_profiles', 'coordinator_profiles');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Role consolidation preserves records but cannot infer each account's former role.
        Schema::rename('coordinator_profiles', 'professor_profiles');
    }
};

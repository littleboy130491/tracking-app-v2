# Activity Log

High-level history of files created, updated, and deleted in this project.

| Date | Agent | Action | File | Description |
| :--- | :--- | :----- | :--- | :---------- |
| 2026-09-15 00:00 | Devin | CREATE | plans/_progress.md | Internal agent progress tracker |
| 2026-09-15 00:00 | Devin | CREATE | docs/ACTIVITY_LOG.md | Project activity log |
| 2026-09-15 03:37 | Devin | UPDATE | composer.json | Added Filament, Shield and otpz dependencies |
| 2026-09-15 03:38 | Devin | CREATE | app/Providers/Filament/AdminPanelProvider.php | Filament admin panel provider |
| 2026-09-15 03:38 | Devin | UPDATE | bootstrap/providers.php | Registered AdminPanelProvider |
| 2026-09-15 03:38 | Devin | UPDATE | .env | App name, Mailgun keys, OTP dev flag |
| 2026-09-15 03:38 | Devin | UPDATE | .env.example | App name, Mailgun keys, OTP dev flag |
| 2026-09-15 03:38 | Devin | UPDATE | config/services.php | Added Mailgun credentials block |
| 2026-09-15 03:37 | Devin | CREATE | config/permission.php | Published spatie permission config |
| 2026-09-15 03:37 | Devin | CREATE | config/filament-shield.php | Published Shield config |
| 2026-09-15 03:37 | Devin | CREATE | config/otpz.php | Published OTP config |
| 2026-09-15 03:37 | Devin | CREATE | database/migrations/2026_09_15_033744_create_permission_tables.php | spatie permission tables |
| 2026-09-15 03:37 | Devin | CREATE | database/migrations/2026_09_15_033745_create_otps_table.php | OTP storage table |
| 2026-09-15 04:00 | Devin | CREATE | database/migrations/2026_09_15_040001_create_customers_table.php | Customers table |
| 2026-09-15 04:00 | Devin | CREATE | database/migrations/2026_09_15_040002_add_is_active_to_users_table.php | Users active flag |
| 2026-09-15 04:00 | Devin | CREATE | database/migrations/2026_09_15_040003_create_customer_user_table.php | User<->customer pivot |
| 2026-09-15 04:00 | Devin | CREATE | database/migrations/2026_09_15_040004_create_workflow_templates_table.php | Workflow templates |
| 2026-09-15 04:00 | Devin | CREATE | database/migrations/2026_09_15_040005_create_workflow_versions_table.php | Workflow versions |
| 2026-09-15 04:00 | Devin | CREATE | database/migrations/2026_09_15_040006_create_bill_of_ladings_table.php | Bill of ladings |
| 2026-09-15 04:00 | Devin | CREATE | database/migrations/2026_09_15_040007_create_bill_of_lading_hs_codes_table.php | B/L HS codes |
| 2026-09-15 04:00 | Devin | CREATE | database/migrations/2026_09_15_040008_create_containers_table.php | Containers |
| 2026-09-15 04:00 | Devin | CREATE | database/migrations/2026_09_15_040009_create_workflow_stages_table.php | Workflow stages |
| 2026-09-15 04:00 | Devin | CREATE | database/migrations/2026_09_15_040010_create_workflow_stage_dependencies_table.php | Stage dependencies |
| 2026-09-15 04:00 | Devin | CREATE | database/migrations/2026_09_15_040011_create_workflow_stage_fields_table.php | Stage field definitions |
| 2026-09-15 04:00 | Devin | CREATE | database/migrations/2026_09_15_040012_create_workflow_stage_permissions_table.php | Stage role permissions |
| 2026-09-15 04:00 | Devin | CREATE | database/migrations/2026_09_15_040013_create_stage_records_table.php | Stage execution records |
| 2026-09-15 04:00 | Devin | CREATE | database/migrations/2026_09_15_040014_create_stage_field_values_table.php | Dynamic field values |
| 2026-09-15 04:00 | Devin | CREATE | database/migrations/2026_09_15_040015_create_container_location_updates_table.php | Container location history |
| 2026-09-15 04:00 | Devin | CREATE | database/migrations/2026_09_15_040016_create_attachments_table.php | Attachments |
| 2026-09-15 04:00 | Devin | CREATE | database/migrations/2026_09_15_040017_create_activity_logs_table.php | Append-only activity logs |
| 2026-09-15 04:00 | Devin | CREATE | database/migrations/2026_09_15_040018_add_is_internal_to_roles_table.php | Roles internal flag |
| 2026-09-15 04:10 | Devin | CREATE | app/Enums/*.php | Status/type enums (20 files) |
| 2026-09-15 04:10 | Devin | UPDATE | app/Models/User.php | Roles, OTP, customers, panel access |
| 2026-09-15 04:10 | Devin | CREATE | app/Models/*.php | Domain models (16 files) |
| 2026-09-15 04:20 | Devin | CREATE | database/seeders/RoleSeeder.php | admin/operator/customer roles |
| 2026-09-15 04:20 | Devin | CREATE | database/seeders/SuperAdminSeeder.php | Default staff accounts |
| 2026-09-15 04:20 | Devin | CREATE | database/seeders/WorkflowSeeder.php | Export/Import workflow definitions |
| 2026-09-15 04:20 | Devin | CREATE | database/seeders/DemoShipmentSeeder.php | Demo customers, B/Ls, containers |
| 2026-09-15 04:20 | Devin | UPDATE | database/seeders/DatabaseSeeder.php | Seeder orchestration |
| 2026-09-15 04:20 | Devin | UPDATE | config/permission.php | Use App\Models\Role |
| 2026-09-15 04:20 | Devin | UPDATE | config/filament-shield.php | admin as super admin via gate |
| 2026-09-15 04:20 | Devin | UPDATE | database/factories/UserFactory.php | Set is_active default |
| 2026-09-15 04:30 | Devin | CREATE | app/Filament/Resources/** | Admin resources, forms, tables |
| 2026-09-15 04:30 | Devin | CREATE | app/Filament/Resources/**/RelationManagers/*.php | B/L containers, HS codes |
| 2026-09-15 04:30 | Devin | CREATE | app/Policies/*.php | Shield-generated policies (8 files) |
| 2026-09-15 04:35 | Devin | CREATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Admin panel smoke tests |
| 2026-09-15 04:45 | Devin | CREATE | app/Services/ActivityLogger.php | Append-only audit writer |
| 2026-09-15 04:45 | Devin | CREATE | app/Services/Workflow/ConditionEvaluator.php | JSON condition evaluation |
| 2026-09-15 04:45 | Devin | CREATE | app/Services/Workflow/StageAuthorizationService.php | Stage role abilities |
| 2026-09-15 04:45 | Devin | CREATE | app/Services/Workflow/DependencyEvaluator.php | Prerequisite resolution |
| 2026-09-15 04:45 | Devin | CREATE | app/Services/Workflow/StageRecordFactory.php | Stage record creation |
| 2026-09-15 04:45 | Devin | CREATE | app/Services/Workflow/WorkflowActivationService.php | Activation + SPJM flow |
| 2026-09-15 04:45 | Devin | CREATE | app/Services/Workflow/StageCompletionService.php | Save/complete/reopen/skip |
| 2026-09-15 04:45 | Devin | CREATE | app/Services/Workflow/ShipmentProgressService.php | Container/BL completion rules |
| 2026-09-15 04:50 | Devin | CREATE | app/Filament/Pages/ShipmentWorkflow.php | Admin stage execution screen |
| 2026-09-15 04:50 | Devin | CREATE | resources/views/filament/pages/shipment-workflow.blade.php | Workflow page view |
| 2026-09-15 04:50 | Devin | CREATE | app/Filament/Concerns/SynchronisesShipmentWorkflow.php | Post-save workflow sync hook |
| 2026-09-15 04:50 | Devin | UPDATE | app/Filament/Resources/**/Pages/*.php | Sync workflow after save |
| 2026-09-15 04:50 | Devin | UPDATE | app/Filament/Resources/BillOfLadings/Tables/BillOfLadingsTable.php | Workflow action |
| 2026-09-15 04:50 | Devin | UPDATE | database/seeders/WorkflowSeeder.php | SPPB gate as validation rule |
| 2026-09-15 04:55 | Devin | CREATE | tests/Feature/Workflow/WorkflowEngineTest.php | Workflow engine tests |
| 2026-09-15 05:00 | Devin | CREATE | app/Support/Otp/RegisteredUserResolver.php | No-registration OTP rule |
| 2026-09-15 05:00 | Devin | CREATE | app/Mail/DevOtpMail.php | Dev OTP exposure |
| 2026-09-15 05:00 | Devin | UPDATE | config/otpz.php | Custom resolver/mailable + dev flag |
| 2026-09-15 05:00 | Devin | CREATE | app/Http/Controllers/Customer/LoginController.php | Passwordless login flow |
| 2026-09-15 05:00 | Devin | CREATE | app/Http/Controllers/Customer/LogoutController.php | Portal logout |
| 2026-09-15 05:00 | Devin | CREATE | app/Livewire/Customer/Dashboard.php | Portal dashboard |
| 2026-09-15 05:00 | Devin | CREATE | app/Livewire/Customer/BillOfLadingDetail.php | Portal BL detail + PIB confirm |
| 2026-09-15 05:00 | Devin | CREATE | app/Livewire/Customer/ContainerDetail.php | Portal container detail |
| 2026-09-15 05:00 | Devin | CREATE | resources/views/customer/*.blade.php | Login and verify screens |
| 2026-09-15 05:00 | Devin | CREATE | resources/views/livewire/customer/*.blade.php | Portal screens |
| 2026-09-15 05:00 | Devin | CREATE | resources/views/components/layouts/portal.blade.php | Portal layout |
| 2026-09-15 05:00 | Devin | UPDATE | routes/web.php | Portal routes |
| 2026-09-15 05:00 | Devin | UPDATE | bootstrap/app.php | Guest redirect to portal login |
| 2026-09-15 05:05 | Devin | UPDATE | phpunit.xml | Force test environment values |
| 2026-09-15 05:05 | Devin | UPDATE | tests/TestCase.php | Pin env before app boot |
| 2026-09-15 05:05 | Devin | CREATE | tests/Feature/Portal/PortalTest.php | Portal tests |
| 2026-09-15 05:05 | Devin | DELETE | tests/Feature/ExampleTest.php | Replaced by feature tests |
| 2026-09-15 05:10 | Devin | UPDATE | app/** tests/** database/** | Pint style fixes |
| 2026-09-15 06:06 | Devin | CREATE | public/images/logo.png | Published brand mark from assets/logo.png |
| 2026-09-15 06:06 | Devin | UPDATE | vite.config.js | Roboto replaces Instrument Sans in @fonts |
| 2026-09-15 06:06 | Devin | UPDATE | resources/css/app.css | Brand tokens (#499bff) + Roboto sans font |
| 2026-09-15 06:06 | Devin | UPDATE | app/Providers/Filament/AdminPanelProvider.php | Brand logo, name, Roboto, primary colour |
| 2026-09-15 06:06 | Devin | CREATE | resources/views/components/brand-logo.blade.php | Shared logo Blade component |
| 2026-09-15 06:06 | Devin | UPDATE | resources/views/components/layouts/portal.blade.php | Brand header + logo + accent bar |
| 2026-09-15 06:06 | Devin | UPDATE | resources/views/customer/login.blade.php | Branded login screen |
| 2026-09-15 06:06 | Devin | UPDATE | resources/views/customer/verify.blade.php | Branded OTP screen |
| 2026-09-15 06:06 | Devin | UPDATE | resources/views/livewire/customer/*.blade.php | Brand colours for links/buttons/inputs |
| 2026-09-15 06:20 | Devin | CREATE | database/seeders/PermissionSeeder.php | Creates Shield permissions on a fresh install |
| 2026-09-15 06:20 | Devin | UPDATE | database/seeders/DatabaseSeeder.php | Run PermissionSeeder before RoleSeeder |
| 2026-09-15 06:25 | Devin | UPDATE | README.md | Project overview, install, config, demo accounts |
| 2026-09-15 07:05 | Devin | CREATE | app/Support/Otp/ConfigurableAttemptOtp.php | Configurable wrong-code attempt cap |
| 2026-09-15 07:05 | Devin | UPDATE | app/Http/Controllers/Customer/LoginController.php | Signed-URL fixes, friendly errors, rate limiter |
| 2026-09-15 07:05 | Devin | UPDATE | resources/views/customer/verify.blade.php | Post back to the signed URL; error alert |
| 2026-09-15 07:05 | Devin | UPDATE | routes/web.php | Signature checked in controller, not middleware |
| 2026-09-15 07:05 | Devin | UPDATE | config/otpz.php | max_attempts, attempt_decay_minutes, expiration |
| 2026-09-15 07:05 | Devin | UPDATE | .env, .env.example | OTP policy variables |
| 2026-09-15 07:20 | Devin | CREATE | database/seeders/DemoCustomerSeeder.php | Five companies and five portal users (M:N) |
| 2026-09-15 07:20 | Devin | UPDATE | database/seeders/DemoShipmentSeeder.php | Completed export and import examples |
| 2026-09-15 07:20 | Devin | UPDATE | database/seeders/WorkflowSeeder.php | Export empty-return stage |
| 2026-09-15 07:20 | Devin | UPDATE | tests/Feature/Portal/PortalTest.php | Form-action, friendly-error and rate-limit tests |
| 2026-09-15 07:20 | Devin | UPDATE | tests/Feature/Workflow/WorkflowEngineTest.php | Export completion via the empty-return stage |
| 2026-09-15 07:45 | Devin | UPDATE | database/seeders/DemoShipmentSeeder.php | Idempotent: engine-owned fields only on create |
| 2026-09-15 07:45 | Devin | CREATE | tests/Feature/Seeders/SeederIdempotencyTest.php | Guards seeders against re-run side effects |
| 2026-09-15 08:05 | Devin | UPDATE | app/Livewire/Customer/Dashboard.php | Company filter |
| 2026-09-15 08:05 | Devin | UPDATE | resources/views/livewire/customer/dashboard.blade.php | Company filter and column |
| 2026-09-15 08:05 | Devin | UPDATE | app/Livewire/Customer/BillOfLadingDetail.php | No confirm/revision once the draft PIB is confirmed |
| 2026-09-15 08:05 | Devin | UPDATE | resources/views/livewire/customer/bill-of-lading-detail.blade.php | Hide draft-PIB actions when confirmed |
| 2026-09-15 08:05 | Devin | UPDATE | tests/Feature/Portal/PortalTest.php | Company filter/column and confirmed-draft tests |
| 2026-09-15 09:00 | Devin | UPDATE | app/Providers/AppServiceProvider.php | Replaces the crashing orderedUuid generator |
| 2026-09-15 09:00 | Devin | UPDATE | routes/web.php | Landing route: staff to the panel, customers to the portal |
| 2026-09-15 09:00 | Devin | UPDATE | bootstrap/app.php | redirectUsersTo the landing route (breaks the redirect loop) |
| 2026-09-15 09:00 | Devin | CREATE | app/Support/Authorization/AssignableRoles.php | Which roles an actor may assign |
| 2026-09-15 09:00 | Devin | UPDATE | app/Models/Role.php | super_admin role + privileged role list |
| 2026-09-15 09:00 | Devin | UPDATE | database/seeders/RoleSeeder.php | Four roles; all permissions for super admin and admin |
| 2026-09-15 09:00 | Devin | UPDATE | database/seeders/SuperAdminSeeder.php | Adds superadmin@example.com |
| 2026-09-15 09:00 | Devin | UPDATE | config/filament-shield.php | super_admin is the gate-bypassing role |
| 2026-09-15 09:00 | Devin | UPDATE | app/Filament/Resources/Users/** | Customer default + restricted role options |
| 2026-09-15 09:00 | Devin | UPDATE | app/Services/Workflow/StageAuthorizationService.php | Admin and super admin bypass stage permissions |
| 2026-09-15 09:00 | Devin | CREATE | tests/Feature/Admin/UserRoleAssignmentTest.php | Role hierarchy tests |
| 2026-09-15 09:00 | Devin | UPDATE | tests/Feature/Portal/PortalTest.php | Redirect-loop regression tests |
| 2026-09-15 10:10 | Devin | UPDATE | composer.json | Adds awcodes/filament-curator |
| 2026-09-15 10:10 | Devin | CREATE | config/curator.php | Curator config; model points at App\Models\Attachment |
| 2026-09-15 10:10 | Devin | CREATE | database/migrations/2026_09_15_050038_create_curator_table.php | Curator media table |
| 2026-09-15 10:10 | Devin | CREATE | database/migrations/2026_09_15_101000_add_shipment_columns_to_curator_table.php | Shipment columns on curator; drops attachments |
| 2026-09-15 10:10 | Devin | DELETE | database/migrations/2026_09_15_040016_create_attachments_table.php | Superseded by Curator |
| 2026-09-15 10:10 | Devin | UPDATE | app/Models/Attachment.php | Now extends Curator's Media |
| 2026-09-15 10:10 | Devin | UPDATE | app/Providers/Filament/AdminPanelProvider.php | Registers the Curator plugin |
| 2026-09-15 10:10 | Devin | UPDATE | app/Services/Workflow/StageCompletionService.php | Links Curator media instead of storing files |
| 2026-09-15 10:10 | Devin | UPDATE | app/Filament/Pages/ShipmentWorkflow.php | CuratorPicker for file fields |
| 2026-09-15 10:10 | Devin | UPDATE | .env, .env.example | CURATOR_DEFAULT_DISK=public |
| 2026-09-15 12:19 | Devin | CREATE | docs/ERD.md | Full-schema Mermaid ER diagram + notes |
| 2026-09-15 12:25 | Devin | UPDATE | database/migrations/0001_01_01_000000_create_users_table.php | Rebuild: +phone, -email_verified_at, folded is_active |
| 2026-09-15 12:25 | Devin | UPDATE | database/migrations/2026_09_15_040003_create_customer_user_table.php | Slim composite PK pivot |
| 2026-09-15 12:25 | Devin | DELETE | database/migrations/2026_09_15_040002_add_is_active_to_users_table.php | Folded into users migration |
| 2026-09-15 12:28 | Devin | UPDATE | app/Models/User.php | +phone fillable, -email_verified_at cast |
| 2026-09-15 12:28 | Devin | UPDATE | database/factories/UserFactory.php | +phone, removed unverified state |
| 2026-09-15 12:32 | Devin | UPDATE | app/Filament/Resources/Users/Schemas/UserForm.php | Added optional phone input |
| 2026-09-15 12:32 | Devin | UPDATE | app/Filament/Resources/Users/Tables/UsersTable.php | Added phone column |
| 2026-09-15 12:32 | Devin | UPDATE | app/Filament/Resources/Customers/Schemas/CustomerForm.php | Removed portal-users select (moved to RM) |
| 2026-09-15 12:32 | Devin | UPDATE | app/Filament/Resources/Customers/CustomerResource.php | Registered UsersRelationManager |
| 2026-09-15 12:32 | Devin | CREATE | app/Filament/Resources/Customers/RelationManagers/UsersRelationManager.php | Portal-user CRUD on customer page |
| 2026-09-15 12:40 | Devin | UPDATE | AGENTS.md | Added §12 mandatory UAT docs rule |
| 2026-09-15 12:40 | Devin | CREATE | docs/UAT.md | UAT checklist for users/customers CRUD |
| 2026-09-15 12:48 | Devin | UPDATE | docs/ERD.md | Synced users/customer_user to rebuilt schema |
| 2026-09-15 12:55 | Devin | UPDATE | style.md | Filament sections default 1-col full width |
| 2026-09-15 12:55 | Devin | UPDATE | app/Filament/Resources/Users/Schemas/UserForm.php | Dropped 2-col sections |
| 2026-09-15 12:55 | Devin | UPDATE | app/Filament/Resources/Customers/Schemas/CustomerForm.php | Dropped 2-col section |
| 2026-09-15 13:02 | Devin | UPDATE | database/migrations/2026_09_15_040001_create_companies_table.php | Renamed from customers; table = companies |
| 2026-09-15 13:02 | Devin | UPDATE | database/migrations/2026_09_15_040003_create_company_user_table.php | Renamed from customer_user; company_id FK |
| 2026-09-15 13:02 | Devin | UPDATE | database/migrations/2026_09_15_040006_create_bill_of_ladings_table.php | company_id + company_name_snapshot |
| 2026-09-15 13:01 | Devin | CREATE | app/Models/Company.php | Renamed from Customer; companies() M:N kept |
| 2026-09-15 13:01 | Devin | DELETE | app/Models/Customer.php | Replaced by Company |
| 2026-09-15 13:01 | Devin | UPDATE | app/Models/User.php | customers() -> companies() |
| 2026-09-15 13:01 | Devin | UPDATE | app/Models/BillOfLading.php | company() relation, company_id/snapshot fillable |
| 2026-09-15 13:01 | Devin | CREATE | app/Policies/CompanyPolicy.php | Renamed from CustomerPolicy; *:Company perms |
| 2026-09-15 13:01 | Devin | DELETE | app/Policies/CustomerPolicy.php | Replaced by CompanyPolicy |
| 2026-09-15 13:01 | Devin | CREATE | app/Filament/Resources/Companies/** | Resource, form, table, pages, UsersRelationManager |
| 2026-09-15 13:01 | Devin | DELETE | app/Filament/Resources/Customers/** | Replaced by Companies resource tree |
| 2026-09-15 13:01 | Devin | UPDATE | app/Filament/Resources/Users/Schemas/UserForm.php | companies relationship select |
| 2026-09-15 13:01 | Devin | UPDATE | app/Filament/Resources/Users/Tables/UsersTable.php | companies_count column |
| 2026-09-15 13:01 | Devin | UPDATE | app/Filament/Resources/BillOfLadings/** | company_id select + company columns/filter |
| 2026-09-15 13:01 | Devin | UPDATE | app/Filament/Resources/Containers/Tables/ContainersTable.php | billOfLading.company.name column |
| 2026-09-15 13:01 | Devin | UPDATE | app/Filament/Pages/ShipmentWorkflow.php + blade | Company label + company_name_snapshot |
| 2026-09-15 13:01 | Devin | UPDATE | app/Livewire/Customer/** + portal views | companies() scoping, company_id, ->company |
| 2026-09-15 13:01 | Devin | CREATE | database/seeders/DemoCompanySeeder.php | Renamed from DemoCustomerSeeder |
| 2026-09-15 13:01 | Devin | DELETE | database/seeders/DemoCustomerSeeder.php | Replaced by DemoCompanySeeder |
| 2026-09-15 13:01 | Devin | UPDATE | database/seeders/{DatabaseSeeder,DemoShipmentSeeder}.php | Company model + company_id refs |
| 2026-09-15 13:01 | Devin | UPDATE | tests/Feature/{Portal,Admin,Seeders,Workflow}/** | Company model + ViewAny:Company perm |
| 2026-09-15 13:01 | Devin | UPDATE | docs/{ERD,UAT}.md + README.md | COMPANIES/COMPANY_USER entities; Companies wording |
| 2026-09-15 13:10 | Devin | UPDATE | app/Providers/Filament/AdminPanelProvider.php | Panel-wide full content width |
| 2026-09-15 13:10 | Devin | UPDATE | style.md | Recorded full-width page convention |
| 2026-09-15 13:25 | Devin | UPDATE | app/Filament/Resources/Companies/Schemas/CompanyForm.php | Portal-users select + inline create |
| 2026-09-15 13:25 | Devin | UPDATE | app/Filament/Resources/Companies/CompanyResource.php | Dropped getRelations() |
| 2026-09-15 13:25 | Devin | DELETE | app/Filament/Resources/Companies/RelationManagers/ | Replaced by form select |
| 2026-09-15 13:25 | Devin | UPDATE | docs/UAT.md | Portal-user items rewritten for select flow |
| 2026-09-15 13:35 | Devin | UPDATE | database/seeders/RoleSeeder.php | Operator loses Delete/Restore perms (admin+ only) |
| 2026-09-15 13:35 | Devin | UPDATE | tests/Feature/Admin/UserRoleAssignmentTest.php | Assert delete lifecycle is admin-only |
| 2026-09-15 13:35 | Devin | UPDATE | README.md | Operator access description updated |
| 2026-09-15 13:45 | Devin | UPDATE | app/Filament/Resources/Users/Schemas/UserForm.php | Removed roles/companies helper texts |
| 2026-09-15 13:55 | Devin | UPDATE | app/Filament/Resources/Users/Schemas/UserForm.php | Password auto-fills Str::password() on create |
| 2026-09-15 13:55 | Devin | UPDATE | app/Filament/Resources/Companies/Schemas/CompanyForm.php | createOptionUsing uses Str::password() |
| 2026-09-15 14:05 | Devin | UPDATE | app/Filament/Resources/Users/Schemas/UserForm.php | Password field visible by default |
| 2026-09-15 14:20 | Devin | UPDATE | app/Filament/Resources/Companies/Schemas/CompanyForm.php | is_active toggle restricted to admin/super admin |
| 2026-09-15 14:20 | Devin | UPDATE | app/Filament/Resources/Users/Schemas/UserForm.php | is_active toggle restricted to admin/super admin |
| 2026-09-15 14:20 | Devin | UPDATE | tests/Feature/Admin/UserRoleAssignmentTest.php | Assert operator toggle is ignored, admin persists |
| 2026-09-15 14:35 | Devin | UPDATE | app/Providers/Filament/AdminPanelProvider.php | Unsaved-changes alert + collapsible sidebar |
| 2026-09-15 14:50 | Devin | UPDATE | app/Filament/Resources/Companies/Tables/CompaniesTable.php | Users column lists emails; removed B/Ls count |
| 2026-09-15 15:05 | Devin | CREATE | app/Filament/Resources/Companies/RelationManagers/BillOfLadingsRelationManager.php | B/L list on company edit page |
| 2026-09-15 15:05 | Devin | UPDATE | app/Filament/Resources/Companies/CompanyResource.php | Registered BillOfLadingsRelationManager |
| 2026-09-15 15:05 | Devin | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Assert company B/L tab scopes correctly |
| 2026-09-15 15:20 | Devin | UPDATE | app/Filament/Resources/Companies/Tables/CompaniesTable.php | Portal-user emails link to user edit page |
| 2026-09-15 15:20 | Devin | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Assert portal-user email links render |
| 2026-09-15 15:40 | Devin | UPDATE | database/seeders/RoleSeeder.php | Operators lose all Company permissions |
| 2026-09-15 15:40 | Devin | UPDATE | tests/Feature/Admin/UserRoleAssignmentTest.php | Assert operator 403 on companies/users pages |
| 2026-09-15 15:40 | Devin | UPDATE | README.md | Operator access description updated |
| 2026-09-15 16:00 | Devin | UPDATE | app/Filament/Resources/*/Pages/Edit*.php (7 pages) | Save button in header; delete actions removed |
| 2026-09-15 16:30 | Devin | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Sections reorganized per export/import spec, type-conditional |
| 2026-09-15 16:30 | Devin | UPDATE | app/Filament/Resources/Containers/Schemas/ContainerForm.php | Sections grouped by process step, hidden by parent shipment type |
| 2026-09-15 16:45 | Devin | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Back to tabs; tab 1 = AJU + customer |
| 2026-09-15 17:00 | Devin | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Stripped to 4-field Document tab + hidden plumbing |
| 2026-09-15 17:00 | Devin | UPDATE | app/Models/BillOfLading.php | Auto-fill company_name_snapshot on create |
| 2026-09-15 17:00 | Devin | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Assert minimal B/L create works |
| 2026-09-15 17:15 | Devin | UPDATE | app/Filament/Resources/*/Pages/Create*.php (7 pages) | Create button in header actions |
| 2026-09-15 17:30 | Devin | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Snapshot shown read-only on edit only |
| 2026-09-15 17:45 | Devin | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Snapshot editable by admin/super-admin on edit |
| 2026-09-15 18:00 | Devin | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Event tab: remaining fields in collapsible per-step sections |
| 2026-09-15 18:15 | Devin | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Containers tab: per-container repeater with event sections |
| 2026-09-15 18:15 | Devin | UPDATE | app/Filament/Resources/BillOfLadings/BillOfLadingResource.php | Removed container/HS-code relation managers |
| 2026-09-15 18:15 | Devin | DELETE | app/Filament/Resources/BillOfLadings/RelationManagers/ | Relation managers replaced by form repeaters |
| 2026-09-15 18:45 | Devin | DELETE | database/migrations/*workflow*/\|*stage* (8 files) | Removed DB-driven workflow schema |
| 2026-09-15 18:45 | Devin | UPDATE | database/migrations/*bill_of_ladings*, *location_updates*, *activity_logs*, *curator* | Dropped workflow/stage FK columns |
| 2026-09-15 18:45 | Devin | DELETE | app/Models/{Workflow*,Stage*}, app/Services/Workflow, app/Filament/{Pages,Concerns}, workflow resources | Removed workflow engine code |
| 2026-09-15 18:45 | Devin | DELETE | app/Enums/{Stage*,Workflow*,Field*,TargetMatch,ActivationMode}, 3 workflow policies | Removed workflow-only types |
| 2026-09-15 18:45 | Devin | UPDATE | B/L + Container pages/tables, company RM, form, models | Stripped workflow actions and relations |
| 2026-09-15 18:45 | Devin | UPDATE | DemoShipmentSeeder, DatabaseSeeder, RoleSeeder | Seeds shipments directly; workflow seeder deleted |
| 2026-09-15 18:45 | Devin | UPDATE | Portal ContainerDetail + views | Removed stage timeline; locations only |
| 2026-09-15 18:45 | Devin | DELETE | tests/Feature/Workflow/ | Engine removed; smoke/portal/idempotency tests updated |
| 2026-09-15 18:45 | Devin | UPDATE | docs/ERD.md, README.md | Documented hardcoded tracking model |
| 2026-09-15 15:28 | Devin | UPDATE | database/migrations/*hs_codes* | HS codes: master table + B/L pivot |
| 2026-09-15 15:28 | Devin | CREATE | app/Models/HsCode.php, app/Policies/HsCodePolicy.php | Shared HS-code model and policy |
| 2026-09-15 15:28 | Devin | DELETE | app/Models/BillOfLadingHsCode.php | Replaced by many-to-many HsCode |
| 2026-09-15 15:28 | Devin | CREATE | app/Filament/Resources/HsCodes/ | Top-level HS-code resource (pages, form, table) |
| 2026-09-15 15:28 | Devin | UPDATE | app/Models/BillOfLading.php, BillOfLadingForm | belongsToMany hsCodes; multi-select + inline create |
| 2026-09-15 15:28 | Devin | CREATE | database/seeders/HsCodeSeeder.php | Starter HS codes; demo shipments attach them |
| 2026-09-15 15:28 | Devin | UPDATE | AdminPanelSmokeTest, docs/ERD.md, README.md | HS-code list/create/attach coverage and docs |
| 2026-09-15 15:50 | Devin | CREATE | app/Enums/ShipmentMilestone.php | Per-type milestone sequence; SPJM branch conditional |
| 2026-09-15 15:50 | Devin | UPDATE | bill_of_ladings migration, app/Models/BillOfLading.php | current_milestone column, cast, advance/regress helpers |
| 2026-09-15 15:50 | Devin | UPDATE | BillOfLadingForm | Event tab → Progress: milestone header, advance/regress, per-step gated sections |
| 2026-09-15 15:50 | Devin | UPDATE | AdminPanelSmokeTest, docs/ERD.md, README.md | Milestone transition + SPJM-skip tests; docs |
| 2026-09-15 15:56 | Devin | UPDATE | BillOfLadingForm, AdminPanelSmokeTest | Gated remaining sections (cargo, status, container identity/add) |
| 2026-09-15 16:05 | Devin | UPDATE | BillOfLadingForm | Progress header visible on create too; actions edit-only via record check |
| 2026-09-15 16:10 | Devin | UPDATE | BillOfLadingForm | Shipment type defaults to export |
| 2026-09-15 16:20 | Devin | UPDATE | BillOfLadingForm, AdminPanelSmokeTest | Simplified progress header to heading + step line |
| 2026-09-15 16:35 | Devin | UPDATE | BillOfLadingForm | Booking-order fields grouped per export spec; schedule section split |
| 2026-09-15 16:45 | Devin | UPDATE | BillOfLadingForm | Progress sections auto-collapse unless at their milestone |
| 2026-09-15 17:05 | Devin | UPDATE | BillOfLadingForm, BillOfLading | Containers repeater moved into Progress pickup step; Activity log tab added |
| 2026-09-15 17:20 | Devin | CREATE | resources/views/filament/bill-of-ladings/milestone-stepper.blade.php | Interactive horizontal milestone stepper |
| 2026-09-15 17:20 | Devin | UPDATE | BillOfLading, EditBillOfLading, BillOfLadingForm, AdminPanelSmokeTest | moveToMilestone jump + activity logging; stepper in progress header |
| 2026-09-15 17:35 | Devin | UPDATE | milestone-stepper.blade.php | Scoped CSS in-file (panel ships precompiled CSS); progress-line styling |
| 2026-09-15 17:50 | Devin | UPDATE | milestone-stepper.blade.php, BillOfLadingForm, EditBillOfLading | Green #03eb62; section wrapper + step text removed; Regress/Advance to page header |
| 2026-09-15 18:05 | Devin | UPDATE | EditBillOfLading, milestone-stepper.blade.php, AdminPanelSmokeTest | Stepper clicks mount Filament modal; forward jumps capped at 1 step |
| 2026-09-16 10:58 | Devin | UPDATE | BillOfLadingForm, AdminPanelSmokeTest | Progress tab restricted to edit; create shows Document only |
| 2026-09-16 11:09 | Devin | UPDATE | BillOfLadingForm | Locked sections now identify their required milestone |
| 2026-09-16 11:12 | Devin | UPDATE | app/Services/ActivityLogger.php | Added shipment snapshots and changed-field diff recording |
| 2026-09-16 11:59 | Devin | UPDATE | app/Filament/Resources/BillOfLadings/Pages/EditBillOfLading.php | Audit B/L, nested container, and HS assignment saves |
| 2026-09-16 11:59 | Devin | UPDATE | app/Filament/Resources/Containers/Pages/EditContainer.php | Audit standalone container field changes |
| 2026-09-16 11:59 | Devin | UPDATE | app/Filament/Resources/Containers/Pages/CreateContainer.php | Audit standalone container creation values |
| 2026-09-16 12:24 | Devin | CREATE | tests/Feature/Admin/ShipmentActivityLoggingTest.php | Cover milestone guidance and all shipment audit paths |
| 2026-09-16 12:43 | Devin | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Added persistent milestone state metadata for soft dividers |
| 2026-09-16 12:43 | Devin | UPDATE | resources/views/filament/bill-of-ladings/milestone-stepper.blade.php | Added scoped current, available, and locked divider styles |
| 2026-09-16 12:47 | Devin | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Flattened top-level Progress groups into soft dividers |
| 2026-09-16 12:58 | Devin | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Flattened nested container groups and kept repeater items open |
| 2026-09-16 12:43 | Devin | UPDATE | tests/Feature/Admin/ShipmentActivityLoggingTest.php | Updated locked-message assertions for persistent milestone context |
| 2026-09-16 13:05 | Devin | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Locked groups keep message; current/available show none |
| 2026-09-16 13:05 | Devin | UPDATE | tests/Feature/Admin/ShipmentActivityLoggingTest.php | Assert no current/available milestone text renders |
| 2026-09-16 13:05 | Devin | UPDATE | docs/UAT.md | Added B/L progress and audit manual checklist |
| 2026-09-16 13:28 | Devin | UPDATE | database/migrations/2026_09_15_040008_create_containers_table.php | Added gate_in_port_name and final_checked; dropped vgm_unit and final_checked_by |
| 2026-09-16 13:28 | Devin | UPDATE | app/Models/Container.php | Fillable/casts for new columns; removed finalCheckedBy relation |
| 2026-09-16 13:28 | Devin | UPDATE | app/Filament/Resources/Containers/Schemas/ContainerForm.php | Added tracking-position repeater, gate-in port, VGM (kg), final-checked checkbox |
| 2026-09-16 13:44 | Devin | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Flat gated fields; added tracking repeater, gate-in port, VGM (kg), final-checked |
| 2026-09-16 13:44 | Devin | UPDATE | resources/views/filament/bill-of-ladings/milestone-stepper.blade.php | Removed obsolete soft-divider CSS |
| 2026-09-16 13:44 | Devin | UPDATE | tests/Feature/Admin/ShipmentActivityLoggingTest.php | Locked messages now assert "Locked until Step X: name" |
| 2026-09-16 14:02 | Devin | UPDATE | database/migrations/2026_09_15_040008_create_containers_table.php | Added tracking_position free-text column |
| 2026-09-16 14:02 | Devin | DELETE | database/migrations/2026_09_15_040015_create_container_location_updates_table.php | Dropped unused location-history table |
| 2026-09-16 14:02 | Devin | DELETE | app/Models/ContainerLocationUpdate.php | Removed with the table |
| 2026-09-16 14:02 | Devin | UPDATE | app/Models/Container.php | Added tracking_position; removed locationUpdates relation |
| 2026-09-16 14:02 | Devin | UPDATE | app/Livewire/Customer/ContainerDetail.php | Portal no longer queries location history |
| 2026-09-16 14:02 | Devin | UPDATE | resources/views/livewire/customer/container-detail.blade.php | History table replaced by tracking position row |
| 2026-09-16 14:15 | Devin | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Tracking position text + Curator attachments picker in container items |
| 2026-09-16 14:15 | Devin | UPDATE | app/Filament/Resources/Containers/Schemas/ContainerForm.php | Same fields on the standalone container form |
| 2026-09-16 14:15 | Devin | UPDATE | app/Models/Container.php | Added syncAttachments() for picker state |
| 2026-09-16 14:15 | Devin | UPDATE | app/Services/ActivityLogger.php | Container snapshots now include attachment ids |
| 2026-09-16 14:15 | Devin | UPDATE | app/Filament/Resources/BillOfLadings/Pages/EditBillOfLading.php | Syncs picked attachments before the audit diff |
| 2026-09-16 14:15 | Devin | UPDATE | app/Filament/Resources/Containers/Pages/EditContainer.php | Same sync on standalone save |
| 2026-09-16 14:15 | Devin | UPDATE | app/Filament/Resources/Containers/Pages/CreateContainer.php | Same sync on standalone create |
| 2026-09-16 14:40 | Devin | UPDATE | database/seeders/DemoShipmentSeeder.php | Completed export container carries full milestone field data |
| 2026-09-16 15:03 | Devin | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Picker seeded via mutateRelationshipDataBeforeFillUsing; clobbering hook removed |
| 2026-09-16 15:03 | Devin | UPDATE | app/Filament/Resources/Containers/Schemas/ContainerForm.php | Removed loadStateFromRelationshipsUsing that reset picker state on save |
| 2026-09-16 15:03 | Devin | UPDATE | app/Filament/Resources/Containers/Pages/EditContainer.php | Seeds attachment picker via mutateFormDataBeforeFill |
| 2026-09-16 15:03 | Devin | UPDATE | app/Filament/Resources/BillOfLadings/Pages/EditBillOfLading.php | Captures picks in beforeSave; syncs by container_number; refreshes repeater |
| 2026-09-16 15:03 | Devin | UPDATE | tests/Feature/Admin/ShipmentActivityLoggingTest.php | Attachment sync test uses real uuid-keyed picker state; passes |
| 2026-09-16 15:10 | Devin | UPDATE | docs/UAT.md | Checklist now covers field-level locking, tracking text, and attachments |
| 2026-09-16 15:15 | Devin | UPDATE | docs/ERD.md | Containers entity matches new schema; location-updates entity removed |
| 2026-09-16 15:15 | Devin | UPDATE | migration_plan.md | Container columns updated; location-updates section removed; renumbered |
| 2026-09-16 15:21 | Muse Spark | CREATE | app/Services/ShipmentTimelineEntry.php | Define customer timeline entry contract |
| 2026-09-16 15:36 | Muse Spark | CREATE | app/Services/ShipmentTimeline.php | Build customer journey from visible logs and shipment dates |
| 2026-09-16 15:42 | Muse Spark | UPDATE | app/Livewire/Customer/ContainerDetail.php | Wire timeline service into portal container page |
| 2026-09-16 15:42 | Muse Spark | UPDATE | resources/views/livewire/customer/container-detail.blade.php | Add sailing block and journey timeline |
| 2026-09-16 15:42 | Muse Spark | CREATE | resources/views/components/shipment-timeline.blade.php | Shared customer journey timeline partial |
| 2026-09-16 15:42 | Muse Spark | UPDATE | app/Livewire/Customer/BillOfLadingDetail.php | Wire timeline service into portal shipment page |
| 2026-09-16 15:42 | Muse Spark | UPDATE | resources/views/livewire/customer/bill-of-lading-detail.blade.php | Show shipment journey timeline above containers |
| 2026-09-16 15:42 | Muse Spark | UPDATE | docs/UAT.md | Add portal journey manual checklist |
| 2026-09-16 15:50 | Muse Spark | UPDATE | app/Services/ShipmentTimeline.php | Add shipment-level latest-entry helper |
| 2026-09-16 15:50 | Muse Spark | UPDATE | app/Livewire/Customer/Dashboard.php | Container search plus latest journey per shipment |
| 2026-09-16 15:50 | Muse Spark | UPDATE | resources/views/livewire/customer/dashboard.blade.php | Show latest place and event columns |
| 2026-09-16 15:50 | Muse Spark | UPDATE | tests/Feature/Portal/PortalTest.php | Cover container search and latest journey columns |
| 2026-09-16 15:50 | Muse Spark | UPDATE | docs/UAT.md | Add dashboard latest-columns checklist |

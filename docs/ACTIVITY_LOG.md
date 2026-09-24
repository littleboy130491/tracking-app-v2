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
| 2026-09-16 15:50 | Devin | UPDATE | app/Models/Company.php | Added customers()/operators() role-filtered relations |
| 2026-09-16 16:00 | Devin | UPDATE | app/Filament/Resources/Companies/Tables/CompaniesTable.php | Customers + Operators columns replace Portal users |
| 2026-09-16 16:00 | Devin | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Assert both role columns render and link |
| 2026-09-17 13:52 | Muse Spark | UPDATE | composer.json | Added stechstudio/filament-impersonate plugin |
| 2026-09-16 16:10 | Devin | UPDATE | app/Filament/Resources/Companies/Schemas/CompanyForm.php | Customers + Operators selects replace portal-users select |
| 2026-09-16 16:10 | Devin | UPDATE | app/Models/Company.php | Added syncLinkedUsers() role-scoped pivot sync |
| 2026-09-16 16:10 | Devin | UPDATE | app/Filament/Resources/Companies/Pages/EditCompany.php | Seed + sync the two role selects |
| 2026-09-16 16:10 | Devin | UPDATE | app/Filament/Resources/Companies/Pages/CreateCompany.php | Link role selects after create |
| 2026-09-16 16:10 | Devin | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Role-select hydration, cross-detach safety, create links |
| 2026-09-17 13:52 | Muse Spark | UPDATE | app/Models/User.php | Impersonation rules: admin+ may act, super_admin targets restricted |
| 2026-09-17 13:52 | Muse Spark | UPDATE | app/Filament/Resources/Users/Tables/UsersTable.php | Added Impersonate row action |
| 2026-09-17 13:52 | Muse Spark | UPDATE | app/Filament/Resources/Users/Pages/EditUser.php | Added Impersonate header action |
| 2026-09-17 13:52 | Muse Spark | UPDATE | resources/views/components/layouts/portal.blade.php | Show impersonation banner with leave link |
| 2026-09-17 13:52 | Muse Spark | UPDATE | tests/Feature/Admin/UserRoleAssignmentTest.php | Impersonation rule tests |
| 2026-09-17 13:52 | Muse Spark | UPDATE | docs/UAT.md | Impersonation manual checklist |
| 2026-09-17 14:10 | Devin | UPDATE | app/Models/User.php | Added companyIds() row-scope helper |
| 2026-09-17 14:20 | Devin | UPDATE | app/Filament/Resources/BillOfLadings/BillOfLadingResource.php | Operators scoped to assigned companies' shipments |
| 2026-09-17 14:20 | Devin | UPDATE | app/Filament/Resources/Containers/ContainerResource.php | Containers scoped via their bill of lading |
| 2026-09-17 14:20 | Devin | UPDATE | app/Filament/Resources/ActivityLogs/ActivityLogResource.php | Logs scoped via visible shipments/containers |
| 2026-09-17 14:52 | Muse Spark | UPDATE | app/Filament/Resources/Users/Tables/UsersTable.php | Companies column lists names linking to company edit |
| 2026-09-17 14:52 | Muse Spark | UPDATE | app/Filament/Resources/Users/Tables/UsersTable.php | Added company filter to users table |
| 2026-09-17 14:52 | Muse Spark | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Assert user company names link to company edit |
| 2026-09-17 14:30 | Devin | UPDATE | app/Models/User.php | Added scopeToAssignedCompanies() query helper |
| 2026-09-17 14:30 | Devin | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Company picker scoped to assigned companies |
| 2026-09-17 14:30 | Devin | UPDATE | app/Filament/Resources/Containers/Schemas/ContainerForm.php | B/L picker scoped to assigned companies |
| 2026-09-17 14:30 | Devin | UPDATE | app/Filament/Resources/BillOfLadings/Tables/BillOfLadingsTable.php | Company filter options scoped |
| 2026-09-17 14:30 | Devin | UPDATE | app/Filament/Resources/Containers/Tables/ContainersTable.php | B/L filter options scoped |
| 2026-09-17 14:52 | Muse Spark | UPDATE | docs/UAT.md | Users companies column + filter checklist |
| 2026-09-17 14:52 | Muse Spark | UPDATE | app/Models/User.php | Added canViewAllShipments() for admin portal scope |
| 2026-09-17 14:45 | Devin | UPDATE | database/seeders/DemoCompanySeeder.php | Links operator@example.com to NUS and SNI |
| 2026-09-17 14:45 | Devin | CREATE | tests/Feature/Admin/OperatorRowScopeTest.php | Operator/admin visibility, 404s, picker scope |
| 2026-09-17 14:45 | Devin | UPDATE | tests/Feature/Portal/PortalTest.php | M:N assertions count customers; operator links covered |
| 2026-09-17 14:45 | Devin | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Cross-detach test compares operator sets before/after |
| 2026-09-17 14:45 | Devin | UPDATE | docs/UAT.md | Operator row-scope checklist; sections renumbered |
| 2026-09-17 14:52 | Muse Spark | UPDATE | app/Support/Otp/RegisteredUserResolver.php | Let admin/super_admin request portal OTP codes |
| 2026-09-17 14:52 | Muse Spark | UPDATE | app/Livewire/Customer/Dashboard.php | Unscoped list, company filter and years for admins |
| 2026-09-17 14:52 | Muse Spark | UPDATE | app/Livewire/Customer/BillOfLadingDetail.php | Admins may open any shipment |
| 2026-09-17 14:52 | Muse Spark | UPDATE | app/Livewire/Customer/ContainerDetail.php | Admins may open any container |
| 2026-09-17 14:52 | Muse Spark | UPDATE | tests/Feature/Portal/PortalTest.php | Admin sees all shipments, opens any record |
| 2026-09-17 14:52 | Muse Spark | UPDATE | docs/UAT.md | Admin portal visibility checklist |
| 2026-09-17 15:05 | Devin | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Containers repeater in collapsible section; items collapsed |
| 2026-09-17 15:05 | Devin | UPDATE | docs/UAT.md | Container collapse checklist items |
| 2026-09-17 15:03 | Muse Spark | CREATE | app/Enums/ShipmentMode.php | FCL / LCL / Air shipment mode enum |
| 2026-09-17 15:03 | Muse Spark | CREATE | database/migrations/2026_09_17_150300_add_shipment_mode_to_bill_of_ladings_table.php | Nullable shipment_mode column |
| 2026-09-17 15:03 | Muse Spark | UPDATE | app/Models/BillOfLading.php | Fillable + cast for shipment_mode |
| 2026-09-17 15:03 | Muse Spark | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Shipment mode on Document tab + Step 2 |
| 2026-09-17 15:03 | Muse Spark | UPDATE | database/seeders/DemoShipmentSeeder.php | Seed FCL/LCL/Air modes on demo shipments |
| 2026-09-17 15:03 | Muse Spark | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Shipment mode required + Step 2 tests |
| 2026-09-17 15:03 | Muse Spark | UPDATE | docs/UAT.md | Shipment mode manual checklist |
| 2026-09-17 15:03 | Muse Spark | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Renamed tabs: Customer / Shipping Details |
| 2026-09-17 15:03 | Muse Spark | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Tab rename assertions |
| 2026-09-17 15:03 | Muse Spark | UPDATE | docs/UAT.md | Tab rename wording |
| 2026-09-17 15:35 | Devin | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Item header shows container number via raw state; live-on-blur |
| 2026-09-17 15:35 | Devin | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Header label renders and updates on rename |
| 2026-09-17 15:17 | Muse Spark | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Customer tab trimmed; shipment type locked on edit |
| 2026-09-17 15:17 | Muse Spark | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | AJU/B/L + mode moved to Shipping Details Step 2 |
| 2026-09-17 15:17 | Muse Spark | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Customer-only create + type-lock tests |
| 2026-09-17 15:17 | Muse Spark | UPDATE | docs/UAT.md | Customer tab + Step 2 checklist |
| 2026-09-17 15:17 | Muse Spark | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Stepper above tabs; AJU/B/L/mode always editable |
| 2026-09-17 15:17 | Muse Spark | UPDATE | docs/UAT.md | Stepper-above-tabs checklist |
| 2026-09-17 16:10 | Devin | CREATE | database/migrations/2026_09_17_160000_create_notes_table.php | Polymorphic notes table with nullable author |
| 2026-09-17 16:10 | Devin | CREATE | app/Models/Note.php | Note model with author, noteable, isEditableBy |
| 2026-09-17 16:10 | Devin | CREATE | app/Models/Concerns/HasNotes.php | MorphMany notes relation trait |
| 2026-09-17 16:10 | Devin | UPDATE | app/Models/{BillOfLading,Container,Company,User}.php | Applied HasNotes trait |
| 2026-09-17 16:20 | Devin | CREATE | database/migrations/2026_09_17_160100_make_bill_of_lading_id_nullable_on_activity_logs_table.php | Notes on companies/users need null B/L |
| 2026-09-17 16:20 | Devin | CREATE | app/Policies/NotePolicy.php | Internal read/create; author-only update/delete |
| 2026-09-17 16:20 | Devin | UPDATE | app/Services/ActivityLogger.php | recordNote() with shipment/container linkage |
| 2026-09-17 16:40 | Devin | CREATE | app/Livewire/NotesPanel.php | Notes add/read/edit/delete with scoped mount and audit |
| 2026-09-17 16:40 | Devin | CREATE | resources/views/livewire/notes-panel.blade.php | Filament-styled notes list and form |
| 2026-09-17 16:40 | Devin | CREATE | tests/Feature/Admin/NotesPanelTest.php | Author rules, visibility, logging, scoping |
| 2026-09-17 16:55 | Devin | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Notes tab next to Activity log |
| 2026-09-17 16:55 | Devin | UPDATE | app/Filament/Resources/{Containers,Companies,Users}/Schemas/*.php | Notes section on edit forms |
| 2026-09-17 16:55 | Devin | UPDATE | tests/Feature/Admin/NotesPanelTest.php | Embed check on all four edit pages |
| 2026-09-17 16:55 | Devin | UPDATE | docs/UAT.md | Notes checklist; fixed duplicated section numbers |
| 2026-09-17 17:20 | Devin | CREATE | database/migrations/2026_09_17_170000_add_export_spec_columns.php | Export-spec columns + latest_event stamps |
| 2026-09-17 16:20 | Devin | UPDATE | app/Services/ActivityLogger.php | Stamps latest_event on every recorded event |
| 2026-09-17 16:55 | Devin | UPDATE | app/Models/Container.php | photoPickers() + category-aware syncAttachments() |
| 2026-09-17 16:55 | Devin | UPDATE | app/Enums/AttachmentCategory.php | Added AdditionalPhoto case |
| 2026-09-17 16:55 | Devin | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Five photo pickers, driver license, tracking url, doc-received fields |
| 2026-09-17 16:55 | Devin | UPDATE | app/Filament/Resources/Containers/Schemas/ContainerForm.php | Same photo pickers and fields on the standalone form |
| 2026-09-17 16:55 | Devin | UPDATE | app/Filament/Resources/{Containers,Companies}/Pages/*.php | Category-aware attachment sync on save |
| 2026-09-17 16:55 | Devin | UPDATE | database/seeders/DemoShipmentSeeder.php | Document-received defaults written during seeding |
| 2026-09-17 16:55 | Devin | UPDATE | tests/Feature/Admin/ShipmentActivityLoggingTest.php | Photo picker sync + latest-event assertions |
| 2026-09-17 16:55 | Devin | UPDATE | docs/UAT.md | Export spec and latest-event checklist |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Customer above stepper; Containers split into own tab |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Customer full width; empty stepper shell hidden on create |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Hide Tabs container on create; only Customer section shows |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Customer relation hidden on edit for non-admin/super-admin |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Renamed snapshot label to "Customer name"; updated docblock |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | docs/UAT.md | Added B/L layout + customer permission checks; renumbered sections |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | resources/views/filament/bill-of-ladings/milestone-stepper.blade.php | Step anchors, goto-click scroll+flash, lock-link styles |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Lock helper text is now a data-bl-ms-goto link |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | tests/Feature/Admin/ShipmentActivityLoggingTest.php | Assert locked helper link anchors |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | docs/UAT.md | Added locked-field jump-to-milestone checks |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Reordered/gated Step 2 fields (AJU..B/L number) at Checking booking order |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | tests/Feature/Admin/ShipmentActivityLoggingTest.php | Advance milestone so gated AJU/B/L fields are auditable |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | docs/UAT.md | Updated Step 2 field order expectations |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Grouped cargo fields into the Step 2 block after B/L number |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | docs/UAT.md | Step 2 order now includes cargo fields |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Grouped Pick up depot/Stuffing date/destination consecutively |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | docs/UAT.md | Added pickup-step field order check |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | CREATE | database/migrations/2026_09_18_100000_move_pickup_stuffing_columns_to_bill_of_ladings.php | Move pickup/stuffing columns to bill_of_ladings |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | app/Models/BillOfLading.php | Fillable/casts for pickup depot, stuffing date/destination |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | app/Models/Container.php | Removed moved pickup/stuffing fields |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Moved 3 fields from container repeater to Shipping Details Step 3 |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/Containers/Schemas/ContainerForm.php | Removed moved pickup/stuffing fields |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | app/Services/ShipmentTimeline.php | Read pickup depot/stuffing destination from the B/L |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | database/seeders/DemoShipmentSeeder.php | Seed moved fields on the B/L |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | docs/UAT.md | Added pickup/stuffing B/L-level checks; removed stale container check |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Reordered container fields; moved photos up; relabelled |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/Containers/Schemas/ContainerForm.php | Matching container/photo field labels |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | docs/UAT.md | Container field order + photo label expectations |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | CREATE | database/migrations/2026_09_18_110000_drop_empty_picked_up_at_from_containers.php | Drop empty_picked_up_at column |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | app/Models/Container.php | Removed empty_picked_up_at fillable/cast |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/BillOfLadings/Schemas/BillOfLadingForm.php | Removed empty_picked_up_at from container repeater |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/Containers/Schemas/ContainerForm.php | Removed field; renamed section to Stuffing |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | app/Services/ShipmentTimeline.php | Dropped "Empty container picked up" row |
| 2026-09-18 00:00 | opencode (deepseek-v4.1-flash) | UPDATE | database/seeders/DemoShipmentSeeder.php | Removed seeded empty pickup timestamp |
| 2026-09-19 07:38 | Devin (DeepSeek V4.1 Flash Max) | CREATE | .env | Local env from example; APP_URL=http://tracking-app-v2.test |
| 2026-09-19 08:06 | Devin (DeepSeek V4.1 Flash Max) | CREATE | plans/split-export-import.md | Design freeze: split Export/Import into separate shipment models |
| 2026-09-19 08:06 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | plans/_progress.md | New split-models checklist; Step 1 done |
| 2026-09-19 08:20 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | plans/split-export-import.md | v2: containers split too; fresh-app approach, no data migration |
| 2026-09-19 08:20 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | plans/_progress.md | v2 scope: container models, fresh migrations and seeders |
| 2026-09-19 08:24 | Devin (DeepSeek V4.1 Flash Max) | CREATE | database/migrations/2026_09_15_040006_create_export_shipments_table.php | Export shipment table (fresh schema) |
| 2026-09-19 08:24 | Devin (DeepSeek V4.1 Flash Max) | CREATE | database/migrations/2026_09_15_040006_create_import_shipments_table.php | Import shipment table (fresh schema) |
| 2026-09-19 08:24 | Devin (DeepSeek V4.1 Flash Max) | CREATE | database/migrations/2026_09_15_040008_create_export_containers_table.php | Export container table (fresh schema) |
| 2026-09-19 08:24 | Devin (DeepSeek V4.1 Flash Max) | CREATE | database/migrations/2026_09_15_040008_create_import_containers_table.php | Import container table (fresh schema) |
| 2026-09-19 08:24 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/migrations/2026_09_15_040007_create_hs_codes_table.php | Two shipment HS-code pivots replace the B/L pivot |
| 2026-09-19 08:24 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/migrations/2026_09_15_040017_create_activity_logs_table.php | Split shipment/container link columns |
| 2026-09-19 08:24 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/migrations/2026_09_15_101000_add_shipment_columns_to_curator_table.php | Split shipment/container link columns |
| 2026-09-19 08:24 | Devin (DeepSeek V4.1 Flash Max) | DELETE | database/migrations/2026_09_15_040006_create_bill_of_ladings_table.php | Replaced by export/import shipment tables |
| 2026-09-19 08:24 | Devin (DeepSeek V4.1 Flash Max) | DELETE | database/migrations/2026_09_15_040008_create_containers_table.php | Replaced by export/import container tables |
| 2026-09-19 08:24 | Devin (DeepSeek V4.1 Flash Max) | DELETE | database/migrations/2026_09_17_150300_add_shipment_mode_to_bill_of_ladings_table.php | Folded into the new shipment tables |
| 2026-09-19 08:24 | Devin (DeepSeek V4.1 Flash Max) | DELETE | database/migrations/2026_09_17_160100_make_bill_of_lading_id_nullable_on_activity_logs_table.php | Folded into the rewritten activity_logs table |
| 2026-09-19 08:24 | Devin (DeepSeek V4.1 Flash Max) | DELETE | database/migrations/2026_09_17_170000_add_export_spec_columns.php | Folded into the new shipment/container tables |
| 2026-09-19 08:24 | Devin (DeepSeek V4.1 Flash Max) | DELETE | database/migrations/2026_09_18_100000_move_pickup_stuffing_columns_to_bill_of_ladings.php | Folded into the new shipment tables |
| 2026-09-19 08:24 | Devin (DeepSeek V4.1 Flash Max) | DELETE | database/migrations/2026_09_18_110000_drop_empty_picked_up_at_from_containers.php | Folded into the new container tables |
| 2026-09-19 08:24 | Devin (DeepSeek V4.1 Flash Max) | CREATE | app/Enums/ExportMilestone.php | Export milestone sequence enum |
| 2026-09-19 08:24 | Devin (DeepSeek V4.1 Flash Max) | CREATE | app/Enums/ImportMilestone.php | Import milestone sequence enum with SPJM branch |
| 2026-09-19 08:24 | Devin (DeepSeek V4.1 Flash Max) | CREATE | app/Enums/ShipmentStatus.php | Shipment status enum (replaces BillOfLadingStatus) |
| 2026-09-19 08:24 | Devin (DeepSeek V4.1 Flash Max) | CREATE | app/Models/Concerns/ActsAsShipment.php | Shared shipment defaults and milestone helpers |
| 2026-09-19 08:24 | Devin (DeepSeek V4.1 Flash Max) | CREATE | app/Models/Concerns/ActsAsContainer.php | Shared container photo pickers and attachment sync |
| 2026-09-19 08:24 | Devin (DeepSeek V4.1 Flash Max) | CREATE | app/Models/ExportShipment.php | Export shipment model |
| 2026-09-19 08:24 | Devin (DeepSeek V4.1 Flash Max) | CREATE | app/Models/ImportShipment.php | Import shipment model |
| 2026-09-19 08:24 | Devin (DeepSeek V4.1 Flash Max) | CREATE | app/Models/ExportContainer.php | Export container model |
| 2026-09-19 08:24 | Devin (DeepSeek V4.1 Flash Max) | CREATE | app/Models/ImportContainer.php | Import container model |
| 2026-09-19 08:24 | Devin (DeepSeek V4.1 Flash Max) | CREATE | tests/Feature/Step2SchemaCheckTest.php | Temporary Step 2 verification test |
| 2026-09-19 08:24 | Devin (DeepSeek V4.1 Flash Max) | DELETE | tests/Feature/Step2SchemaCheckTest.php | Removed after the verification passed |
| 2026-09-19 08:30 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Services/ActivityLogger.php | Reworked for the split shipment/container models |
| 2026-09-19 08:30 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Services/ShipmentTimeline.php | forShipment/forContainer with split log links |
| 2026-09-19 08:30 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Models/ActivityLog.php | Split shipment/container links, relations and helpers |
| 2026-09-19 08:30 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Models/Attachment.php | Split shipment/container links, relations and helpers |
| 2026-09-19 08:30 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Models/HsCode.php | Two shipment pivots replace the B/L pivot |
| 2026-09-19 08:30 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Models/Company.php | exportShipments/importShipments relations |
| 2026-09-19 08:30 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Models/Concerns/ActsAsShipment.php | Milestone changes now written to the audit log |
| 2026-09-19 08:30 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Models/Concerns/ActsAsContainer.php | Renamed attachment link constants |
| 2026-09-19 08:30 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Models/ExportShipment.php | Activity-log key helper |
| 2026-09-19 08:30 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Models/ImportShipment.php | Activity-log key helper |
| 2026-09-19 08:30 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Models/ExportContainer.php | Activity-log key helpers + link constants |
| 2026-09-19 08:30 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Models/ImportContainer.php | Activity-log key helpers + link constants |
| 2026-09-19 08:30 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Models/Note.php | Docblock: noteable targets renamed |
| 2026-09-19 08:30 | Devin (DeepSeek V4.1 Flash Max) | CREATE | tests/Feature/Step3ServicesCheckTest.php | Temporary Step 3 verification test |
| 2026-09-19 08:30 | Devin (DeepSeek V4.1 Flash Max) | DELETE | tests/Feature/Step3ServicesCheckTest.php | Removed after 6 tests / 29 assertions passed |
| 2026-09-19 08:40 | Devin (DeepSeek V4.1 Flash Max) | CREATE | app/Filament/Concerns/ShipmentFields.php | Shared shipment form builders + milestone gating |
| 2026-09-19 08:40 | Devin (DeepSeek V4.1 Flash Max) | CREATE | app/Filament/Concerns/ContainerFields.php | Shared container field groups |
| 2026-09-19 08:40 | Devin (DeepSeek V4.1 Flash Max) | CREATE | app/Filament/Resources/ExportShipments/** | Export shipment resource, form, table and pages |
| 2026-09-19 08:40 | Devin (DeepSeek V4.1 Flash Max) | CREATE | app/Filament/Resources/ImportShipments/** | Import shipment resource, form, table and pages |
| 2026-09-19 08:40 | Devin (DeepSeek V4.1 Flash Max) | CREATE | app/Filament/Resources/ExportContainers/** | Export container resource, form, table and pages |
| 2026-09-19 08:40 | Devin (DeepSeek V4.1 Flash Max) | CREATE | app/Filament/Resources/ImportContainers/** | Import container resource, form, table and pages |
| 2026-09-19 08:40 | Devin (DeepSeek V4.1 Flash Max) | CREATE | app/Policies/ExportShipmentPolicy.php | Shield policy for export shipments |
| 2026-09-19 08:40 | Devin (DeepSeek V4.1 Flash Max) | CREATE | app/Policies/ImportShipmentPolicy.php | Shield policy for import shipments |
| 2026-09-19 08:40 | Devin (DeepSeek V4.1 Flash Max) | CREATE | app/Policies/ExportContainerPolicy.php | Shield policy for export containers |
| 2026-09-19 08:40 | Devin (DeepSeek V4.1 Flash Max) | CREATE | app/Policies/ImportContainerPolicy.php | Shield policy for import containers |
| 2026-09-19 08:40 | Devin (DeepSeek V4.1 Flash Max) | CREATE | app/Filament/Resources/Companies/RelationManagers/ExportShipmentsRelationManager.php | Company page export shipment list |
| 2026-09-19 08:40 | Devin (DeepSeek V4.1 Flash Max) | CREATE | app/Filament/Resources/Companies/RelationManagers/ImportShipmentsRelationManager.php | Company page import shipment list |
| 2026-09-19 08:40 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/Companies/CompanyResource.php | Two shipment relation managers |
| 2026-09-19 08:40 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ActivityLogs/Tables/ActivityLogsTable.php | Shipment/container columns via split links |
| 2026-09-19 08:40 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ActivityLogs/Schemas/ActivityLogForm.php | Shipment/container context fields |
| 2026-09-19 08:40 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ActivityLogs/ActivityLogResource.php | Operator scope + eager loading for split links |
| 2026-09-19 08:40 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/HsCodes/Tables/HsCodesTable.php | Export/import shipment counts |
| 2026-09-19 08:40 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/HsCodes/HsCodeResource.php | Moved to the Master data menu group |
| 2026-09-19 08:40 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Livewire/NotesPanel.php | Note targets: the four split models |
| 2026-09-19 08:40 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Enums/{BillingIssuanceStatus,BillingPaymentStatus,BillingResponse,DraftPibConfirmationStatus,ShipmentMode,ShipmentType}.php | Docblocks point at the split models |
| 2026-09-19 08:40 | Devin (DeepSeek V4.1 Flash Max) | DELETE | app/Filament/Resources/BillOfLadings/ (6 files) | Replaced by Export/Import shipment resources |
| 2026-09-19 08:40 | Devin (DeepSeek V4.1 Flash Max) | DELETE | app/Filament/Resources/Containers/ (6 files) | Replaced by Export/Import container resources |
| 2026-09-19 08:40 | Devin (DeepSeek V4.1 Flash Max) | DELETE | app/Filament/Resources/Companies/RelationManagers/BillOfLadingsRelationManager.php | Replaced by two per-process managers |
| 2026-09-19 08:40 | Devin (DeepSeek V4.1 Flash Max) | DELETE | app/Policies/BillOfLadingPolicy.php | Replaced by two shipment policies |
| 2026-09-19 08:40 | Devin (DeepSeek V4.1 Flash Max) | DELETE | app/Policies/ContainerPolicy.php | Replaced by two container policies |
| 2026-09-19 08:40 | Devin (DeepSeek V4.1 Flash Max) | CREATE | tests/Feature/Step4AdminCheckTest.php | Temporary Step 4 verification test |
| 2026-09-19 08:40 | Devin (DeepSeek V4.1 Flash Max) | DELETE | tests/Feature/Step4AdminCheckTest.php | Removed after 5 tests / 34 assertions passed |
| 2026-09-19 08:50 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Livewire/Customer/Dashboard.php | Export/Import tabs for the split models |
| 2026-09-19 08:50 | Devin (DeepSeek V4.1 Flash Max) | CREATE | app/Livewire/Customer/ExportShipmentDetail.php | Export shipment portal page |
| 2026-09-19 08:50 | Devin (DeepSeek V4.1 Flash Max) | CREATE | app/Livewire/Customer/ImportShipmentDetail.php | Import shipment portal page + draft PIB actions |
| 2026-09-19 08:50 | Devin (DeepSeek V4.1 Flash Max) | CREATE | app/Livewire/Customer/ExportContainerDetail.php | Export container portal page |
| 2026-09-19 08:50 | Devin (DeepSeek V4.1 Flash Max) | CREATE | app/Livewire/Customer/ImportContainerDetail.php | Import container portal page |
| 2026-09-19 08:50 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/dashboard.blade.php | Tabs + split shipment list |
| 2026-09-19 08:50 | Devin (DeepSeek V4.1 Flash Max) | CREATE | resources/views/livewire/customer/{export,import}-shipment-detail.blade.php | Per-process shipment detail views |
| 2026-09-19 08:50 | Devin (DeepSeek V4.1 Flash Max) | CREATE | resources/views/livewire/customer/{export,import}-container-detail.blade.php | Per-process container detail views |
| 2026-09-19 08:50 | Devin (DeepSeek V4.1 Flash Max) | CREATE | resources/views/livewire/customer/partials/*.blade.php | Summary, containers and sailing partials |
| 2026-09-19 08:50 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | routes/web.php | Per-process portal routes |
| 2026-09-19 08:50 | Devin (DeepSeek V4.1 Flash Max) | DELETE | app/Livewire/Customer/{BillOfLadingDetail,ContainerDetail}.php | Replaced by per-process components |
| 2026-09-19 08:50 | Devin (DeepSeek V4.1 Flash Max) | DELETE | resources/views/livewire/customer/{bill-of-lading-detail,container-detail}.blade.php | Replaced by per-process views |
| 2026-09-19 08:50 | Devin (DeepSeek V4.1 Flash Max) | CREATE | tests/Feature/Step5PortalCheckTest.php | Temporary Step 5 verification test |
| 2026-09-19 08:50 | Devin (DeepSeek V4.1 Flash Max) | DELETE | tests/Feature/Step5PortalCheckTest.php | Removed after 5 tests / 25 assertions passed |
| 2026-09-19 08:57 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/migrations/2026_09_15_040006_create_import_shipments_table.php | Added the missing do_number column |
| 2026-09-19 08:57 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Models/ImportShipment.php | do_number fillable |
| 2026-09-19 08:57 | Devin (DeepSeek V4.1 Flash Max) | CREATE | database/seeders/DemoExportShipmentSeeder.php | Demo export shipments + containers |
| 2026-09-19 08:57 | Devin (DeepSeek V4.1 Flash Max) | CREATE | database/seeders/DemoImportShipmentSeeder.php | Demo import shipments + containers |
| 2026-09-19 08:57 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/seeders/DatabaseSeeder.php | Runs the two per-process demo seeders |
| 2026-09-19 08:57 | Devin (DeepSeek V4.1 Flash Max) | DELETE | database/seeders/DemoShipmentSeeder.php | Replaced by per-process seeders |
| 2026-09-19 08:57 | Devin (DeepSeek V4.1 Flash Max) | DELETE | app/Models/BillOfLading.php | Replaced by ExportShipment/ImportShipment |
| 2026-09-19 08:57 | Devin (DeepSeek V4.1 Flash Max) | DELETE | app/Models/Container.php | Replaced by ExportContainer/ImportContainer |
| 2026-09-19 08:57 | Devin (DeepSeek V4.1 Flash Max) | DELETE | app/Enums/BillOfLadingStatus.php | Replaced by ShipmentStatus |
| 2026-09-19 08:57 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Rewritten for the split resources |
| 2026-09-19 08:57 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Admin/ShipmentActivityLoggingTest.php | Rewritten for the split events and links |
| 2026-09-19 08:57 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Admin/OperatorRowScopeTest.php | Rewritten for the split menus |
| 2026-09-19 08:57 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Admin/NotesPanelTest.php | Note targets on the split models |
| 2026-09-19 08:57 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Admin/UserRoleAssignmentTest.php | Permission names for the split models |
| 2026-09-19 08:57 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Portal/PortalTest.php | Rewritten for tabs and per-process routes |
| 2026-09-19 08:57 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Seeders/SeederIdempotencyTest.php | Split snapshot + completed shipments |
| 2026-09-19 08:57 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Concerns/ShipmentFields.php | Activity-log container entry via state closure |
| 2026-09-19 08:57 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | phpunit.xml | CURATOR_GLIDE_TOKEN for tests |
| 2026-09-19 08:57 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | .env | CURATOR_GLIDE_TOKEN via curator:token |
| 2026-09-19 09:05 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | README.md | Split models, menus, routes, config and demo data |
| 2026-09-19 09:05 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | New split-models checklist; stale sections updated |
| 2026-09-19 09:05 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/ERD.md | Split schema ERD + notes |
| 2026-09-19 09:05 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | migration_plan.md | Status banner pointing at the split schema |
| 2026-09-19 09:05 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | IMPORT.md | Removed the five removed fields from the process spec |
| 2026-09-19 09:05 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | plans/split-export-import.md | Marked EXECUTED (7/7 phases) |
| 2026-09-19 09:05 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | plans/_progress.md | Final summary; all seven steps done |
| 2026-09-19 09:08 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ExportShipments/ExportShipmentResource.php | Navigation: Bill of Ladings → Export |
| 2026-09-19 09:08 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ImportShipments/ImportShipmentResource.php | Navigation: Bill of Ladings → Import |
| 2026-09-19 09:08 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ExportContainers/ExportContainerResource.php | Navigation: Containers → Export |
| 2026-09-19 09:08 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ImportContainers/ImportContainerResource.php | Navigation: Containers → Import |
| 2026-09-19 09:08 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Navigation group/label regression test |
| 2026-09-19 09:08 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Menu paths use the Bill of Ladings / Containers groups |
| 2026-09-19 09:08 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | README.md | Menu paths updated |
| 2026-09-19 09:08 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | plans/split-export-import.md | D2 records the final menu structure |
| 2026-09-19 09:11 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Providers/Filament/AdminPanelProvider.php | Navigation group order: Bill of Ladings, Containers, CRM, Master data, Monitoring |
| 2026-09-19 09:11 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Rendered-sidebar group-order regression test |
| 2026-09-19 09:11 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Menu checklist states the top-to-bottom group order |
| 2026-09-19 09:14 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Livewire/Customer/Dashboard.php | Type dropdown replaces the Export/Import tab buttons |
| 2026-09-19 09:14 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/dashboard.blade.php | Type select in the filter row (7-column grid) |
| 2026-09-19 09:14 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Portal/PortalTest.php | Type-filter assertions replace switchType calls |
| 2026-09-19 09:14 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Portal checklist uses the Type dropdown wording |
| 2026-09-19 09:14 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | README.md | Portal Type dropdown wording |
| 2026-09-19 09:17 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/dashboard.blade.php | Removed the greeting subtitle under the portal heading |
| 2026-09-19 09:24 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ExportShipments/Schemas/ExportShipmentForm.php | Step 2 starts with B/L number, then DO, AJU |
| 2026-09-19 09:24 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/migrations/2026_09_15_040006_create_{export,import}_shipments_table.php | Dropped reference_number; the B/L number is the identifier |
| 2026-09-19 09:24 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Models/{ExportShipment,ImportShipment}.php | reference_number removed from fillable |
| 2026-09-19 09:24 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Concerns/ShipmentFields.php | hiddenReferenceNumber removed |
| 2026-09-19 09:24 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/{Export,Import}Shipments/** | Reference column/field removed; record title = B/L number |
| 2026-09-19 09:24 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/{Export,Import}Containers/** | Shipment pickers, filters and columns use the B/L number |
| 2026-09-19 09:24 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ActivityLogs/** + Companies/RelationManagers/** | Shipment references show the B/L number |
| 2026-09-19 09:24 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Livewire/Customer/Dashboard.php + customer views | Portal identifies shipments by B/L number; reference search removed |
| 2026-09-19 09:24 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/seeders/{DemoExportShipmentSeeder,DemoImportShipmentSeeder}.php | Seeders keyed by B/L number |
| 2026-09-19 09:24 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/** | Fixtures moved from REF-* to BL-* |
| 2026-09-19 09:24 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | README.md, docs/{UAT,ERD}.md, plans/split-export-import.md | Reference number removed from the docs |
| 2026-09-19 09:26 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ExportShipments/Schemas/ExportShipmentForm.php | HS codes field removed from the export form (import-only) |
| 2026-09-19 09:26 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md, README.md | HS codes documented as import-only |
| 2026-09-19 09:29 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Concerns/ShipmentFields.php | containersTab accepts header components above the repeater |
| 2026-09-19 09:29 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ExportShipments/Schemas/ExportShipmentForm.php | Pick up depot / Stuffing date / destination moved to the Containers tab |
| 2026-09-19 09:29 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Pickup/stuffing checks moved to the Containers tab |
| 2026-09-19 09:32 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Concerns/ContainerFields.php | Tracking position + url no longer force a full-width row |
| 2026-09-19 09:32 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ExportShipments/Schemas/ExportShipmentForm.php | Tracking fields side by side (50/50) in the container repeater |
| 2026-09-19 09:32 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ExportContainers/Schemas/ExportContainerForm.php | Tracking fields side by side on the standalone container form |
| 2026-09-19 09:32 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Tracking-field row expectation added |
| 2026-09-19 09:36 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/migrations/2026_09_15_040008_create_export_containers_table.php | Dropped stuffing_started_at and stuffing_finished_at |
| 2026-09-19 09:36 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Models/ExportContainer.php | Stuffing timestamps removed from fillable/casts |
| 2026-09-19 09:36 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Concerns/ContainerFields.php | Stuffing timestamps removed from the export fields |
| 2026-09-19 09:36 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Services/ShipmentTimeline.php | Stuffing started/finished journey rows removed |
| 2026-09-19 09:36 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/seeders/DemoExportShipmentSeeder.php | Stuffing timestamps no longer seeded |
| 2026-09-19 09:36 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/{ERD,UAT}.md, migration_plan.md, plans/split-export-import.md | Stuffing timestamps removed from the docs |
| 2026-09-19 09:39 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/migrations/2026_09_15_040008_create_export_containers_table.php | gate_in_port_name renamed to port_of_loading |
| 2026-09-19 09:39 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Models/ExportContainer.php | port_of_loading in fillable |
| 2026-09-19 09:39 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Concerns/ContainerFields.php | Checking PEB & NPE field is now Port of loading (default from B/L, overridable) |
| 2026-09-19 09:39 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Services/ShipmentTimeline.php | Gate-in journey row reads the container port of loading |
| 2026-09-19 09:39 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/seeders/DemoExportShipmentSeeder.php | Seeds port_of_loading on the completed export container |
| 2026-09-19 09:39 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/{ERD,UAT}.md, plans/split-export-import.md | Port of loading rename documented |
| 2026-09-19 09:42 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ExportShipments/Schemas/ExportShipmentForm.php | Container Status + Completed at removed from the repeater |
| 2026-09-19 09:42 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ExportContainers/Schemas/ExportContainerForm.php | Status section removed from the standalone export container form |
| 2026-09-19 09:42 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Concerns/ContainerFields.php | status() documented as import-only |
| 2026-09-19 09:42 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Admin/ShipmentActivityLoggingTest.php | Container create test no longer fills status |
| 2026-09-19 09:45 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ExportShipments/Schemas/ExportShipmentForm.php | Sailing dates + Status/Completed at removed from the export form |
| 2026-09-19 09:45 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Export form field list updated |
| 2026-09-19 09:47 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Concerns/ShipmentFields.php | New statusTab() builder (Status + Completed at) |
| 2026-09-19 09:47 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ExportShipments/Schemas/ExportShipmentForm.php | Status tab added after Containers |
| 2026-09-19 09:47 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Edit page asserts the Status tab |
| 2026-09-19 09:47 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Status tab checklist items added |
| 2026-09-19 09:51 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Concerns/ContainerFields.php | Stuffing status label clarified to "Stuffing status at Factory" |
| 2026-09-19 09:51 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Container checklist uses the new label |
| 2026-09-19 09:54 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Enums/StuffingStatus.php | Reduced to On Process / Finished |
| 2026-09-19 09:54 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/migrations/2026_09_15_040008_create_export_containers_table.php | stuffing_status defaults to on_process |
| 2026-09-19 09:54 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Concerns/ContainerFields.php | Stuffing status defaults to On Process |
| 2026-09-19 09:54 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Admin/ShipmentActivityLoggingTest.php | Container create test uses on_process |
| 2026-09-19 09:54 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Stuffing status values documented |
| 2026-09-19 09:57 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/migrations/2026_09_15_040006_create_export_shipments_table.php | stuffing_date is a timestamp now |
| 2026-09-19 09:57 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Models/ExportShipment.php | stuffing_date casts to datetime |
| 2026-09-19 09:57 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ExportShipments/Schemas/ExportShipmentForm.php | Stuffing date uses a date + time picker |
| 2026-09-19 09:57 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/seeders/DemoExportShipmentSeeder.php | Seeds a stuffing datetime |
| 2026-09-19 09:57 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Stuffing date documented as date + time |
| 2026-09-19 10:03 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Models/Concerns/ActsAsShipment.php | visibleInPortal scope; drafts publish on milestone progress |
| 2026-09-19 10:03 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Livewire/Customer/Dashboard.php | Portal list hides draft shipments |
| 2026-09-19 10:03 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Livewire/Customer/{Export,Import}ShipmentDetail.php | Draft shipments 404 in the portal |
| 2026-09-19 10:03 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Livewire/Customer/{Export,Import}ContainerDetail.php | Containers of drafts 404 in the portal |
| 2026-09-19 10:03 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Portal/PortalTest.php | Draft hidden + publishes on advance |
| 2026-09-19 10:03 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Draft publishes on advance; cancelled stays |
| 2026-09-19 10:03 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Draft visibility rule documented |
| 2026-09-19 10:06 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Concerns/ContainerFields.php | Final checked is a toggle now |
| 2026-09-19 10:06 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Final checked noted as a toggle |
| 2026-09-21 16:20 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Filament/Resources/ExportShipments/Schemas/ExportShipmentForm.php | AJU number field removed from the export form |
| 2026-09-21 16:20 | Devin (GLM-5.3 Flash Max) | UPDATE | tests/Feature/Admin/ShipmentActivityLoggingTest.php | Audit test no longer fills/asserts aju_number |
| 2026-09-21 16:25 | Devin (GLM-5.3 Flash Max) | UPDATE | docs/UAT.md | Export Shipping Details checklists drop AJU number |
| 2026-09-21 13:40 | Devin (GLM-5.3 Flash Max) | CREATE | resources/css/filament/admin/theme.css | Custom admin theme; imports Curator picker styles |
| 2026-09-21 13:40 | Devin (GLM-5.3 Flash Max) | UPDATE | vite.config.js | Theme CSS added to the Vite inputs |
| 2026-09-21 13:40 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Providers/Filament/AdminPanelProvider.php | Panel registers the custom theme via viteTheme |
| 2026-09-21 13:40 | Devin (GLM-5.3 Flash Max) | UPDATE | package.json | tailwindcss bumped to ^4.3.3 by make:filament-theme |
| 2026-09-21 13:55 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Filament/Concerns/ShipmentFields.php | Containers repeater tagged with bl-containers class |
| 2026-09-21 13:55 | Devin (GLM-5.3 Flash Max) | UPDATE | resources/css/filament/admin/theme.css | Container repeater item headers tinted brand blue |
| 2026-09-21 14:10 | Devin (GLM-5.3 Flash Max) | UPDATE | resources/css/filament/admin/theme.css | Curator picker cards full-width with filled previews |
| 2026-09-21 13:57 | Grok (Grok 4.6) | UPDATE | resources/views/livewire/notes-panel.blade.php | Real textarea + roomier composer field and button |
| 2026-09-21 13:57 | Grok (Grok 4.6) | UPDATE | resources/css/filament/admin/theme.css | Notes composer spacing; scan livewire views |
| 2026-09-21 13:57 | Grok (Grok 4.6) | UPDATE | docs/UAT.md | Notes tab expects a tall textarea above Add note |
| 2026-09-21 13:57 | Grok (Grok 4.6) | UPDATE | plans/_progress.md | Notes composer spacing tweak recorded |
| 2026-09-21 14:20 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Filament/Concerns/ContainerFields.php | Photo pickers limited to one photo per slot |
| 2026-09-21 14:20 | Devin (GLM-5.3 Flash Max) | UPDATE | tests/Feature/Admin/ShipmentActivityLoggingTest.php | Picker sync test picks a single photo |
| 2026-09-21 14:35 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Filament/Resources/ExportShipments/Schemas/ExportShipmentForm.php | AJU number field returns, gated at Step 5 above the repeater |
| 2026-09-21 14:05 | Grok (Grok 4.6) | UPDATE | resources/views/filament/bill-of-ladings/milestone-stepper.blade.php | More vertical padding on the milestone stepper |
| 2026-09-21 14:05 | Grok (Grok 4.6) | UPDATE | docs/UAT.md | Stepper checklist notes the extra vertical padding |
| 2026-09-21 14:05 | Grok (Grok 4.6) | UPDATE | plans/_progress.md | Milestone stepper padding tweak recorded |
| 2026-09-21 14:26 | Grok (Grok 4.6) | UPDATE | app/Models/Concerns/ActsAsShipment.php | pickerLabel() fallback when B/L number is empty |
| 2026-09-21 14:26 | Grok (Grok 4.6) | UPDATE | app/Filament/Resources/ExportContainers/Tables/ExportContainersTable.php | Shipment filter uses pickerLabel() |
| 2026-09-21 14:26 | Grok (Grok 4.6) | UPDATE | app/Filament/Resources/ExportContainers/Schemas/ExportContainerForm.php | Shipment select uses pickerLabel() |
| 2026-09-21 14:26 | Grok (Grok 4.6) | UPDATE | app/Filament/Resources/ImportContainers/Tables/ImportContainersTable.php | Shipment filter uses pickerLabel() |
| 2026-09-21 14:26 | Grok (Grok 4.6) | UPDATE | app/Filament/Resources/ImportContainers/Schemas/ImportContainerForm.php | Shipment select uses pickerLabel() |
| 2026-09-21 14:26 | Grok (Grok 4.6) | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Container lists with a null B/L number |
| 2026-09-21 14:26 | Grok (Grok 4.6) | UPDATE | docs/UAT.md | Checklist for container lists without a B/L |
| 2026-09-21 14:26 | Grok (Grok 4.6) | UPDATE | plans/_progress.md | Null B/L picker fix recorded |
| 2026-09-21 15:10 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Enums/ImportMilestone.php | Rebuilt to the 22-step IMPORT.md sequence |
| 2026-09-21 15:10 | Devin (GLM-5.3 Flash Max) | UPDATE | database/migrations/2026_09_15_040006_create_import_shipments_table.php | Spec columns only; confirmation_checklist added |
| 2026-09-21 15:10 | Devin (GLM-5.3 Flash Max) | UPDATE | database/migrations/2026_09_15_040008_create_import_containers_table.php | Tracking columns added; type/seal/inspection dropped |
| 2026-09-21 15:10 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Models/ImportShipment.php | Fillable/casts follow the spec schema |
| 2026-09-21 15:10 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Models/ImportContainer.php | Fillable/casts follow the spec schema |
| 2026-09-21 15:10 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Enums/FactoryLoadingStatus.php | Reduced to On Process / Finished |
| 2026-09-21 15:10 | Devin (GLM-5.3 Flash Max) | DELETE | app/Enums/InspectionStatus.php | Inspection fields dropped from import |
| 2026-09-21 15:10 | Devin (GLM-5.3 Flash Max) | DELETE | app/Enums/BillingPaymentStatus.php | Payment status fields dropped from import |
| 2026-09-21 15:10 | Devin (GLM-5.3 Flash Max) | DELETE | app/Enums/DraftPibConfirmationStatus.php | Replaced by the confirmation checklist boolean |
| 2026-09-21 15:10 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Filament/Resources/ImportShipments/Schemas/ImportShipmentForm.php | Fields re-gated to IMPORT.md steps; Status tab added |
| 2026-09-21 15:10 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Filament/Concerns/ContainerFields.php | Import groups split per spec; tracking fields added |
| 2026-09-21 15:10 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Filament/Resources/ImportContainers/Schemas/ImportContainerForm.php | Standalone form follows the new groups |
| 2026-09-21 15:10 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Filament/Resources/ImportContainers/Tables/ImportContainersTable.php | Type/seal/inspection columns and filter removed |
| 2026-09-21 15:10 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Livewire/Customer/ImportShipmentDetail.php | Draft PIB confirm uses the confirmation checklist |
| 2026-09-21 15:10 | Devin (GLM-5.3 Flash Max) | UPDATE | resources/views/livewire/customer/import-shipment-detail.blade.php | Checklist state shown; notes block removed |
| 2026-09-21 15:10 | Devin (GLM-5.3 Flash Max) | UPDATE | resources/views/livewire/customer/import-container-detail.blade.php | Seal block becomes Driver |
| 2026-09-21 15:10 | Devin (GLM-5.3 Flash Max) | UPDATE | resources/views/livewire/customer/partials/shipment-containers.blade.php | Seal column hides when no container has one |
| 2026-09-21 15:10 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Livewire/Customer/Dashboard.php | Seal search only on export containers |
| 2026-09-21 15:10 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Services/ShipmentTimeline.php | Import journey rows follow the new fields |
| 2026-09-21 15:10 | Devin (GLM-5.3 Flash Max) | UPDATE | database/seeders/DemoImportShipmentSeeder.php | Seeder rebuilt for the spec schema |
| 2026-09-21 15:10 | Devin (GLM-5.3 Flash Max) | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | SPJM branch + HS code steps follow the new sequence |
| 2026-09-21 15:10 | Devin (GLM-5.3 Flash Max) | UPDATE | tests/Feature/Admin/ShipmentActivityLoggingTest.php | Locked-step text and HS code milestone updated |
| 2026-09-21 15:10 | Devin (GLM-5.3 Flash Max) | UPDATE | tests/Feature/Portal/PortalTest.php | Draft PIB tests use the confirmation checklist |
| 2026-09-21 15:10 | Devin (GLM-5.3 Flash Max) | UPDATE | docs/UAT.md | Import checklist follows IMPORT.md |
| 2026-09-21 15:30 | Devin (GLM-5.3 Flash Max) | UPDATE | database/seeders/DemoExportShipmentSeeder.php | Milestones reflect seeded data (Step 3 / final step) |
| 2026-09-21 15:30 | Devin (GLM-5.3 Flash Max) | UPDATE | database/seeders/DemoImportShipmentSeeder.php | Milestones reflect seeded data (bahandle / final step) |
| 2026-09-21 15:30 | Devin (GLM-5.3 Flash Max) | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Milestone-walk tests rewind the seeded shipment |
| 2026-09-21 15:30 | Devin (GLM-5.3 Flash Max) | UPDATE | tests/Feature/Admin/ShipmentActivityLoggingTest.php | Locked-step test rewinds the seeded shipment |
| 2026-09-21 15:40 | Devin (GLM-5.3 Flash Max) | UPDATE | database/seeders/DemoExportShipmentSeeder.php | Completed export seeds its AJU number (AJU-EXP-0003) |
| 2026-09-21 15:14 | Grok (Grok 4.6) | UPDATE | app/Filament/Resources/ExportShipments/Tables/ExportShipmentsTable.php | Container numbers listed as clickable badges |
| 2026-09-21 15:14 | Grok (Grok 4.6) | UPDATE | app/Filament/Resources/ImportShipments/Tables/ImportShipmentsTable.php | Container numbers listed as clickable badges |
| 2026-09-21 15:14 | Grok (Grok 4.6) | UPDATE | app/Filament/Resources/Companies/RelationManagers/ExportShipmentsRelationManager.php | Company B/L table links container numbers |
| 2026-09-21 15:14 | Grok (Grok 4.6) | UPDATE | app/Filament/Resources/Companies/RelationManagers/ImportShipmentsRelationManager.php | Company B/L table links container numbers |
| 2026-09-21 15:14 | Grok (Grok 4.6) | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Asserts B/L tables link container numbers |
| 2026-09-21 15:14 | Grok (Grok 4.6) | UPDATE | docs/UAT.md | Clickable container badges on the B/L table |
| 2026-09-21 15:14 | Grok (Grok 4.6) | UPDATE | plans/_progress.md | Clickable B/L container column recorded |
| 2026-09-21 16:00 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Filament/Concerns/ShipmentFields.php | Empty unlocked inputs marked with bl-empty-field |
| 2026-09-21 16:00 | Devin (GLM-5.3 Flash Max) | UPDATE | resources/css/filament/admin/theme.css | Amber tint for empty editable fields |
| 2026-09-21 16:00 | Devin (GLM-5.3 Flash Max) | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Marker appears only for unlocked empty fields |
| 2026-09-21 16:10 | Devin (GLM-5.3 Flash Max) | UPDATE | resources/css/filament/admin/theme.css | Empty-field highlight switched from amber to green |
| 2026-09-21 16:20 | Devin (GLM-5.3 Flash Max) | UPDATE | resources/css/filament/admin/theme.css | Empty-field highlight is border-only (green ring) |
| 2026-09-21 16:30 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Filament/Resources/ExportShipments/Pages/EditExportShipment.php | Header action New export B/L |
| 2026-09-21 16:30 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Filament/Resources/ImportShipments/Pages/EditImportShipment.php | Header action New import B/L |
| 2026-09-21 16:30 | Devin (GLM-5.3 Flash Max) | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Header create action exists with the right URL |
| 2026-09-21 16:30 | Devin (GLM-5.3 Flash Max) | UPDATE | docs/UAT.md | New B/L header action documented on both edit forms |
| 2026-09-21 16:40 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Filament/Resources/ImportShipments/Schemas/ImportShipmentForm.php | Containers tab moved before Status |
| 2026-09-21 16:55 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Enums/ImportMilestone.php | Tambahan step SPJM is no longer a milestone step |
| 2026-09-21 16:55 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Filament/Resources/ImportShipments/Schemas/ImportShipmentForm.php | Conditional SPJM info notice; size gated at Upload all document |
| 2026-09-21 16:55 | Devin (GLM-5.3 Flash Max) | UPDATE | resources/views/filament/bill-of-ladings/milestone-stepper.blade.php | Stepper wraps at max 10 steps per row |
| 2026-09-21 16:55 | Devin (GLM-5.3 Flash Max) | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | SPJM branch + notice tests follow the new sequence |
| 2026-09-21 16:55 | Devin (GLM-5.3 Flash Max) | UPDATE | docs/UAT.md | Import checklist: 21 steps, SPJM notice, size gate |
| 2026-09-21 17:10 | Devin (GLM-5.3 Flash Max) | UPDATE | resources/views/filament/bill-of-ladings/milestone-stepper.blade.php | Stepper renders hard rows of max 10 steps |
| 2026-09-21 17:10 | Devin (GLM-5.3 Flash Max) | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Stepper rows hold at most ten steps |
| 2026-09-21 17:30 | Devin (GLM-5.3 Flash Max) | UPDATE | resources/views/filament/bill-of-ladings/milestone-stepper.blade.php | SPJM steps red on their own row with response billing |
| 2026-09-21 17:30 | Devin (GLM-5.3 Flash Max) | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Stepper row layout + red SPJM block asserted |
| 2026-09-21 17:50 | Devin (GLM-5.3 Flash Max) | CREATE | database/seeders/DemoSpjmShipmentSeeder.php | SPJM demo shipments (completed + fresh response) |
| 2026-09-21 17:50 | Devin (GLM-5.3 Flash Max) | UPDATE | database/seeders/DatabaseSeeder.php | Registers the SPJM demo seeder |
| 2026-09-21 17:50 | Devin (GLM-5.3 Flash Max) | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Asserts the SPJM demo shipments |
| 2026-09-21 17:50 | Devin (GLM-5.3 Flash Max) | UPDATE | tests/Feature/Seeders/SeederIdempotencyTest.php | Three completed shipments now |
| 2026-09-21 17:50 | Devin (GLM-5.3 Flash Max) | UPDATE | docs/UAT.md | SPJM demo data checklist |
| 2026-09-21 18:00 | Devin (deepseek-v4.1-flash) | UPDATE | app/Enums/BillingResponse.php | Response column colors: SPJM red, AP/SPJK yellow, SPPB green |
| 2026-09-21 18:00 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/ImportShipments/Tables/ImportShipmentsTable.php | Response badge uses BillingResponse::color() |
| 2026-09-21 18:05 | Devin (deepseek-v4.1-flash) | CREATE | database/migrations/2026_09_21_180000_add_soft_deletes_to_companies_table.php | Add deleted_at to companies |
| 2026-09-21 18:05 | Devin (deepseek-v4.1-flash) | CREATE | database/migrations/2026_09_21_180100_add_soft_deletes_to_users_table.php | Add deleted_at to users |
| 2026-09-21 18:05 | Devin (deepseek-v4.1-flash) | CREATE | database/migrations/2026_09_21_180200_add_soft_deletes_to_hs_codes_table.php | Add deleted_at to hs_codes |
| 2026-09-21 18:20 | Devin (deepseek-v4.1-flash) | UPDATE | database/migrations/2026_09_15_040001_create_companies_table.php | Add softDeletes to companies create table |
| 2026-09-21 18:20 | Devin (deepseek-v4.1-flash) | UPDATE | database/migrations/0001_01_01_000000_create_users_table.php | Add softDeletes to users create table |
| 2026-09-21 18:20 | Devin (deepseek-v4.1-flash) | UPDATE | database/migrations/2026_09_15_040007_create_hs_codes_table.php | Add softDeletes to hs_codes create table |
| 2026-09-21 18:20 | Devin (deepseek-v4.1-flash) | DELETE | database/migrations/2026_09_21_180*_add_soft_deletes_to_*_table.php | Replaced by edits to the original create tables |
| 2026-09-21 18:35 | Devin (deepseek-v4.1-flash) | UPDATE | app/Models/Company.php | Add SoftDeletes trait |
| 2026-09-21 18:35 | Devin (deepseek-v4.1-flash) | UPDATE | app/Models/HsCode.php | Add SoftDeletes trait |
| 2026-09-21 18:35 | Devin (deepseek-v4.1-flash) | UPDATE | app/Models/User.php | Add SoftDeletes; block trashed users from the panel |
| 2026-09-21 18:35 | Devin (deepseek-v4.1-flash) | UPDATE | app/Support/Otp/RegisteredUserResolver.php | Reject soft-deleted users on OTP login |
| 2026-09-21 18:35 | Devin (deepseek-v4.1-flash) | UPDATE | app/Http/Controllers/Customer/LoginController.php | Reject soft-deleted users after OTP verify |
| 2026-09-21 19:10 | Devin (deepseek-v4.1-flash) | UPDATE | composer.json | Added pxlrbt/filament-excel for CSV export |
| 2026-09-21 19:10 | Devin (deepseek-v4.1-flash) | CREATE | app/Filament/Concerns/TableExportColumns.php | Full-field CSV columns incl. relationships |
| 2026-09-21 19:10 | Devin (deepseek-v4.1-flash) | CREATE | app/Filament/Concerns/PrunableTableHeaderAction.php | Admin-only prune-old-data header action |
| 2026-09-21 19:10 | Devin (deepseek-v4.1-flash) | CREATE | app/Services/Prune/OldDataPruner.php | Deletes records older than 3 years |
| 2026-09-21 19:10 | Devin (deepseek-v4.1-flash) | UPDATE | app/Models/User.php | canExportTables() and canPruneOldData() guards |
| 2026-09-21 19:10 | Devin (deepseek-v4.1-flash) | UPDATE | database/seeders/PermissionSeeder.php | Baseline Prune:LegacyData permission |
| 2026-09-21 19:10 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/ExportShipments/Tables/ExportShipmentsTable.php | CSV export + prune header actions |
| 2026-09-21 19:10 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/ImportShipments/Tables/ImportShipmentsTable.php | CSV export + prune header actions |
| 2026-09-21 19:10 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/ExportContainers/Tables/ExportContainersTable.php | CSV export + prune header actions |
| 2026-09-21 19:10 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/ImportContainers/Tables/ImportContainersTable.php | CSV export + prune header actions |
| 2026-09-21 19:10 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/Companies/Tables/CompaniesTable.php | CSV export + prune header actions |
| 2026-09-21 19:10 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/Users/Tables/UsersTable.php | CSV export + prune header actions |
| 2026-09-21 19:10 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/HsCodes/Tables/HsCodesTable.php | CSV export + prune header actions |
| 2026-09-21 19:10 | Devin (deepseek-v4.1-flash) | CREATE | tests/Feature/Admin/TableExportAndPruneTest.php | Covers export/prune actions and column sets |
| 2026-09-21 18:55 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/Companies/Tables/CompaniesTable.php | Add trashed filter + restore/force-delete bulk actions |
| 2026-09-21 18:55 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/Users/Tables/UsersTable.php | Add trashed filter + restore/force-delete bulk actions |
| 2026-09-21 18:55 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/HsCodes/Tables/HsCodesTable.php | Add trashed filter + restore/force-delete bulk actions |
| 2026-09-21 18:55 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/Companies/CompanyResource.php | Allow edit route binding for trashed companies |
| 2026-09-21 18:55 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/Users/UserResource.php | Allow edit route binding for trashed users |
| 2026-09-21 18:55 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/HsCodes/HsCodeResource.php | Allow edit route binding for trashed HS codes |
| 2026-09-21 19:10 | Devin (deepseek-v4.1-flash) | UPDATE | database/seeders/RoleSeeder.php | Super admin only gets ForceDelete; admin gets soft delete + restore |
| 2026-09-21 19:10 | Devin (deepseek-v4.1-flash) | UPDATE | app/Models/User.php | canPruneOldData now super_admin only |
| 2026-09-21 19:10 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Concerns/PrunableTableHeaderAction.php | Wording: prune restricted to super admin |
| 2026-09-21 19:35 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/ExportShipments/Tables/ExportShipmentsTable.php | Hide force-delete unless ForceDeleteAny permitted |
| 2026-09-21 19:35 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/ImportShipments/Tables/ImportShipmentsTable.php | Hide force-delete unless ForceDeleteAny permitted |
| 2026-09-21 19:35 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/ExportContainers/Tables/ExportContainersTable.php | Hide force-delete unless ForceDeleteAny permitted |
| 2026-09-21 19:35 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/ImportContainers/Tables/ImportContainersTable.php | Hide force-delete unless ForceDeleteAny permitted |
| 2026-09-21 19:35 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/Companies/Tables/CompaniesTable.php | Hide force-delete unless ForceDeleteAny permitted |
| 2026-09-21 19:35 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/Users/Tables/UsersTable.php | Hide force-delete unless ForceDeleteAny permitted |
| 2026-09-21 19:35 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/HsCodes/Tables/HsCodesTable.php | Hide force-delete unless ForceDeleteAny permitted |
| 2026-09-21 19:35 | Devin (deepseek-v4.1-flash) | UPDATE | app/Services/Prune/OldDataPruner.php | Include trashed rows in prune scope |
| 2026-09-21 19:35 | Devin (deepseek-v4.1-flash) | UPDATE | app/Livewire/NotesPanel.php | Resolve trashed Company/User for notes |
| 2026-09-21 19:35 | Devin (deepseek-v4.1-flash) | UPDATE | tests/Feature/Admin/UserRoleAssignmentTest.php | Delete-hierarchy + soft-delete/restore/force-delete tests |
| 2026-09-21 19:35 | Devin (deepseek-v4.1-flash) | UPDATE | tests/Feature/Admin/TableExportAndPruneTest.php | Prune moved from admin to super_admin |
| 2026-09-21 19:45 | Devin (deepseek-v4.1-flash) | UPDATE | docs/UAT.md | Delete-rights-by-role checklist; prune now super admin |
| 2026-09-21 19:45 | Devin (deepseek-v4.1-flash) | UPDATE | docs/ERD.md | Note soft deletes on shipments/containers/users/companies/HS codes |
| 2026-09-21 20:05 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/ExportShipments/Tables/ExportShipmentsTable.php | Replace ETA column with Created/Updated |
| 2026-09-21 20:05 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/ImportShipments/Tables/ImportShipmentsTable.php | Replace ETA column with Created/Updated |
| 2026-09-21 20:05 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/Companies/RelationManagers/ExportShipmentsRelationManager.php | Replace ETA column with Created/Updated |
| 2026-09-21 20:05 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/Companies/RelationManagers/ImportShipmentsRelationManager.php | Replace ETA column with Created/Updated |
| 2026-09-21 20:05 | Devin (deepseek-v4.1-flash) | UPDATE | tests/Feature/Admin/TableExportAndPruneTest.php | Assert Created/Updated columns replace ETA |
| 2026-09-21 20:25 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/ExportShipments/Tables/ExportShipmentsTable.php | Add Loading/Discharge location columns |
| 2026-09-21 20:25 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/ImportShipments/Tables/ImportShipmentsTable.php | Add Loading/Discharge location columns |
| 2026-09-21 20:25 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/Companies/RelationManagers/ExportShipmentsRelationManager.php | Add Loading/Discharge location columns |
| 2026-09-21 20:25 | Devin (deepseek-v4.1-flash) | UPDATE | app/Filament/Resources/Companies/RelationManagers/ImportShipmentsRelationManager.php | Add Loading/Discharge location columns |
| 2026-09-21 20:45 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Models/User.php | internal() scope: users with an internal role only |
| 2026-09-21 20:45 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Filament/Concerns/ShipmentFields.php | Document received by lists staff only |
| 2026-09-21 20:45 | Devin (GLM-5.3 Flash Max) | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Staff-only picker test |
| 2026-09-21 20:45 | Devin (GLM-5.3 Flash Max) | UPDATE | docs/UAT.md | Document received by staff-only checklist |
| 2026-09-21 17:08 | Devin (Fusion) | CREATE | RECOMMENDATION_PLAN.md | Added advisory import workflow analysis and future recommendations |
| 2026-09-21 17:22 | Devin (Fusion) | UPDATE | RECOMMENDATION_PLAN.md | Removed Markdown trailing whitespace from customer timeline |
| 2026-09-21 17:24 | Devin (Fusion) | UPDATE | RECOMMENDATION_PLAN.md | Synced recommendations with new import cargo and loading fields |
| 2026-09-21 21:10 | Devin (GLM-5.3 Flash Max) | UPDATE | database/migrations/2026_09_15_040006_create_import_shipments_table.php | packages, terminal_name, loading_date, loading_destination |
| 2026-09-21 21:10 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Models/ImportShipment.php | New cargo/loading fields in fillable and casts |
| 2026-09-21 21:10 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Filament/Resources/ImportShipments/Schemas/ImportShipmentForm.php | Cargo/loading fields above the containers repeater |
| 2026-09-21 21:10 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Filament/Concerns/TableExportColumns.php | Import CSV export includes the new fields |
| 2026-09-21 21:10 | Devin (GLM-5.3 Flash Max) | UPDATE | database/seeders/DemoImportShipmentSeeder.php | Seeds packages/terminal/loading per milestone |
| 2026-09-21 21:10 | Devin (GLM-5.3 Flash Max) | UPDATE | database/seeders/DemoSpjmShipmentSeeder.php | Completed SPJM seeds cargo/loading data |
| 2026-09-21 21:10 | Devin (GLM-5.3 Flash Max) | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Containers tab header fields + gate test |
| 2026-09-21 21:10 | Devin (GLM-5.3 Flash Max) | UPDATE | docs/UAT.md | Containers tab header fields checklist |
| 2026-09-21 21:25 | Devin (GLM-5.3 Flash Max) | UPDATE | app/Filament/Resources/ImportShipments/Schemas/ImportShipmentForm.php | Removed the SPJM info notice |
| 2026-09-21 21:25 | Devin (GLM-5.3 Flash Max) | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Dropped the SPJM notice test |
| 2026-09-21 21:25 | Devin (GLM-5.3 Flash Max) | UPDATE | docs/UAT.md | SPJM notice item removed |
| 2026-09-21 17:59 | Devin (Fusion) | UPDATE | plans/_progress.md | Started import tracking URL implementation plan |
| 2026-09-21 18:05 | Devin (Fusion) | UPDATE | composer.json | Added Laravel Boost development dependency |
| 2026-09-21 18:05 | Devin (Fusion) | UPDATE | composer.lock | Locked Laravel Boost development dependency |
| 2026-09-21 18:05 | Devin (Fusion) | UPDATE | AGENTS.md | Installed Laravel Boost project guidance |
| 2026-09-21 18:05 | Devin (Fusion) | UPDATE | CLAUDE.md | Installed Laravel Boost project guidance |
| 2026-09-21 18:05 | Devin (Fusion) | CREATE | boost.json | Laravel Boost agent configuration |
| 2026-09-21 18:05 | Devin (Fusion) | CREATE | .mcp.json | Laravel Boost MCP server registration |
| 2026-09-21 18:05 | Devin (Fusion) | CREATE | opencode.json | Laravel Boost OpenCode configuration |
| 2026-09-21 18:05 | Devin (Fusion) | CREATE | .agents/skills/** | Laravel Boost skill files for agent use |
| 2026-09-21 18:05 | Devin (Fusion) | CREATE | .claude/skills/** | Laravel Boost skill files for agent use |
| 2026-09-21 18:05 | Devin (Fusion) | CREATE | .grok/skills/** | Laravel Boost skill files for agent use |
| 2026-09-21 18:40 | Devin (Fusion) | UPDATE | database/migrations/2026_09_15_040008_create_import_containers_table.php | tracking URL, container cargo columns |
| 2026-09-21 18:40 | Devin (Fusion) | CREATE | database/migrations/2026_09_21_111540_create_import_container_hs_code_table.php | Container-level HS code pivot |
| 2026-09-21 18:40 | Devin (Fusion) | UPDATE | app/Models/ImportContainer.php | Cargo fields, HS codes relation, shipment defaults |
| 2026-09-21 18:40 | Devin (Fusion) | UPDATE | app/Models/HsCode.php | importContainers relation |
| 2026-09-21 18:40 | Devin (Fusion) | UPDATE | app/Filament/Concerns/ContainerFields.php | Tracking URL, import cargo group, HS codes field |
| 2026-09-21 18:40 | Devin (Fusion) | UPDATE | app/Filament/Concerns/ShipmentFields.php | Repeater seeding hook for new containers |
| 2026-09-21 18:40 | Devin (Fusion) | UPDATE | app/Filament/Resources/ImportShipments/Schemas/ImportShipmentForm.php | Cargo fields to Response billing, container seeding |
| 2026-09-21 18:40 | Devin (Fusion) | UPDATE | app/Filament/Resources/ImportContainers/Schemas/ImportContainerForm.php | Container cargo fields |
| 2026-09-21 18:40 | Devin (Fusion) | UPDATE | app/Filament/Resources/ImportContainers/Pages/CreateImportContainer.php | Cargo and HS code seeding from shipment |
| 2026-09-21 18:40 | Devin (Fusion) | UPDATE | app/Filament/Resources/ImportContainers/Pages/EditImportContainer.php | Cargo defaults from shipment on fill |
| 2026-09-21 18:40 | Devin (Fusion) | UPDATE | app/Filament/Concerns/TableExportColumns.php | Import container export uses new fields |
| 2026-09-21 18:40 | Devin (Fusion) | UPDATE | database/seeders/DemoImportShipmentSeeder.php | Container cargo demo data |
| 2026-09-21 18:40 | Devin (Fusion) | UPDATE | database/seeders/DemoSpjmShipmentSeeder.php | Tracking URL demo value |
| 2026-09-21 18:40 | Devin (Fusion) | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Tracking URL, cargo placement and inheritance tests |
| 2026-09-21 18:40 | Devin (Fusion) | UPDATE | IMPORT.md | Cargo fields and tracking URL moved to Response billing |
| 2026-09-21 18:40 | Devin (Fusion) | UPDATE | docs/ERD.md | Import cargo columns and container HS code pivot |
| 2026-09-21 18:40 | Devin (Fusion) | UPDATE | docs/UAT.md | Import cargo and tracking URL checklist |
| 2026-09-21 18:40 | Devin (Fusion) | UPDATE | RECOMMENDATION_PLAN.md | Tracking URL and cargo placement findings synced |
| 2026-09-22 00:00 | opencode (mimo-v2.6-flash) | UPDATE | AGENTS.md | Removed Laravel Boost guidelines block |
| 2026-09-22 00:05 | opencode (mimo-v2.6-flash) | CREATE | docs/agent-templates.md | Moved long templates out of AGENTS.md |
| 2026-09-22 00:05 | opencode (mimo-v2.6-flash) | UPDATE | AGENTS.md | Replaced templates with pointers to agent-templates.md |
| 2026-09-22 00:10 | opencode (mimo-v2.6-flash) | UPDATE | AGENTS.md | Simplified UAT section to brief checklist rules |
| 2026-09-22 10:58 | Devin (DeepSeek V4.1 Flash Max) | CREATE | app/Filament/Concerns/DateFilters.php | Interface skeleton for the shared date filters |
| 2026-09-22 10:58 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | plans/_progress.md | Started the admin date-filter plan |
| 2026-09-22 11:01 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Concerns/DateFilters.php | Implemented the Year, Month and range filters |
| 2026-09-22 11:01 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ImportShipments/Tables/ImportShipmentsTable.php | Added the created-date filters to the import B/L list |
| 2026-09-22 11:01 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ExportShipments/Tables/ExportShipmentsTable.php | Added the created-date filters to the export B/L list |
| 2026-09-22 11:01 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ImportContainers/Tables/ImportContainersTable.php | Added the created-date filters to the import container list |
| 2026-09-22 11:01 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ExportContainers/Tables/ExportContainersTable.php | Added the created-date filters to the export container list |
| 2026-09-22 11:01 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/Companies/RelationManagers/ImportShipmentsRelationManager.php | Added the created-date filters to the company tab |
| 2026-09-22 11:01 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/Companies/RelationManagers/ExportShipmentsRelationManager.php | Added the created-date filters to the company tab |
| 2026-09-22 11:10 | Devin (DeepSeek V4.1 Flash Max) | CREATE | tests/Feature/Admin/TableDateFiltersTest.php | Year, month and range tests for the six lists |
| 2026-09-22 11:10 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Date-filter checklist for the admin lists |
| 2026-09-22 11:08 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/migrations/2026_09_15_040006_create_export_shipments_table.php | Removed goods_description column |
| 2026-09-22 11:08 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Models/ExportShipment.php | Removed goods_description from fillable |
| 2026-09-22 11:08 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ExportShipments/Schemas/ExportShipmentForm.php | Removed goods_description field |
| 2026-09-22 11:08 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Concerns/TableExportColumns.php | Dropped goods_description from export CSV columns |
| 2026-09-22 11:08 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/seeders/DemoExportShipmentSeeder.php | Removed export goods_description seed values |
| 2026-09-22 11:08 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/partials/shipment-summary.blade.php | Made goods description row import-only |
| 2026-09-22 11:12 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/ERD.md | Removed goods_description from export shipments entity |
| 2026-09-22 11:12 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | plans/split-export-import.md | Marked goods_description as import-only |
| 2026-09-22 11:12 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Updated export Shipping Details expectations |
| 2026-09-22 11:15 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Added export B/L CSV check for removed goods description |
| 2026-09-22 11:23 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Import cargo fields now expected on the Containers tab |
| 2026-09-22 11:24 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ImportShipments/Schemas/ImportShipmentForm.php | Moved cargo fields to the Containers tab header |
| 2026-09-22 11:39 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Concerns/ShipmentFields.php | Fix Add-to-containers no-op; preserve append then seed cargo |
| 2026-09-22 11:39 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Regression test for add-container action |
| 2026-09-22 11:39 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Added Add-to-containers verification checklist |
| 2026-09-22 11:53 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Concerns/ContainerFields.php | Gross weight (kg) after size; drop unit field |
| 2026-09-22 11:53 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ImportShipments/Schemas/ImportShipmentForm.php | Move weight+CBM after size at Response billing |
| 2026-09-22 11:53 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ImportContainers/Schemas/ImportContainerForm.php | Weight+CBM into Container section after size |
| 2026-09-22 11:53 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Models/ImportContainer.php | Drop gross_weight_unit from fillable |
| 2026-09-22 11:53 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/migrations/2026_09_15_040008_create_import_containers_table.php | Drop gross_weight_unit column, re-localize weight/CBM |
| 2026-09-22 11:53 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Concerns/TableExportColumns.php | Remove gross_weight_unit from import container export |
| 2026-09-22 11:53 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/seeders/DemoImportShipmentSeeder.php | Remove gross_weight_unit seed value |
| 2026-09-22 11:53 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/seeders/DemoSpjmShipmentSeeder.php | Remove gross_weight_unit seed values |
| 2026-09-22 11:53 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/import-container-detail.blade.php | Show Gross weight (kg) without unit suffix |
| 2026-09-22 11:53 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/ERD.md | Remove gross_weight_unit from import containers |
| 2026-09-22 11:53 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | IMPORT.md | Gross weight/CBM attach at Response billing |
| 2026-09-22 11:53 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Updated container field order and milestones |
| 2026-09-22 12:06 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Concerns/ContainerFields.php | Drop Loading-in-factory date; drop export container type |
| 2026-09-22 12:06 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Models/ImportContainer.php | Remove factory_loading_at from fillable/casts |
| 2026-09-22 12:06 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Models/ExportContainer.php | Remove type from fillable |
| 2026-09-22 12:06 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Services/ShipmentTimeline.php | Remove Loading-in-factory timeline entry |
| 2026-09-22 12:06 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Concerns/TableExportColumns.php | Drop factory_loading_at and export container type |
| 2026-09-22 12:06 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ImportContainers/Tables/ImportContainersTable.php | Add company filter, clickable company column |
| 2026-09-22 12:06 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ExportContainers/Tables/ExportContainersTable.php | Add company filter, clickable company column, drop Type column |
| 2026-09-22 12:06 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/migrations/2026_09_15_040008_create_import_containers_table.php | Drop factory_loading_at column |
| 2026-09-22 12:06 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/migrations/2026_09_15_040008_create_export_containers_table.php | Drop type column |
| 2026-09-22 12:06 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/seeders/DemoExportShipmentSeeder.php | Remove container type seeding |
| 2026-09-22 12:06 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/seeders/DemoImportShipmentSeeder.php | Remove factory_loading_at seed value |
| 2026-09-22 12:06 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/seeders/DemoSpjmShipmentSeeder.php | Remove factory_loading_at seed values |
| 2026-09-22 12:06 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/export-container-detail.blade.php | Show Size only |
| 2026-09-22 12:06 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/import-container-detail.blade.php | Show Size only |
| 2026-09-22 12:06 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/partials/shipment-containers.blade.php | Show Size only |
| 2026-09-22 12:06 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Admin/ShipmentActivityLoggingTest.php | Drop container type from audit test |
| 2026-09-22 12:06 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/ERD.md | Drop factory_loading_at and export container type |
| 2026-09-22 12:06 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Drop Container Type and Loading-in-factory date |
| 2026-09-22 12:06 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Added container company filter and dropped-field checks |
| 2026-09-22 15:59 | Devin (DeepSeek V4.1 Flash Max) | CREATE | database/seeders/DemoExportConditionSeeder.php | Export states: draft, early, cancelled, LCL/air |
| 2026-09-22 15:59 | Devin (DeepSeek V4.1 Flash Max) | CREATE | database/seeders/DemoImportConditionSeeder.php | Import states: draft, early, AP/SPJK, cancelled |
| 2026-09-22 15:59 | Devin (DeepSeek V4.1 Flash Max) | CREATE | database/seeders/DemoContainerConditionSeeder.php | Containers covering every status |
| 2026-09-22 15:59 | Devin (DeepSeek V4.1 Flash Max) | CREATE | database/seeders/DemoAttachmentSeeder.php | Placeholder container photos via GD |
| 2026-09-22 15:59 | Devin (DeepSeek V4.1 Flash Max) | CREATE | database/seeders/DemoActivityLogSeeder.php | Demo audit trail and notes |
| 2026-09-22 15:59 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/seeders/DatabaseSeeder.php | Register the five condition seeders |
| 2026-09-22 15:59 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Models/Concerns/ActsAsShipment.php | Clamp milestone when billing response changes |
| 2026-09-22 15:59 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ImportShipments/Pages/EditImportShipment.php | Jump guard uses live billing response |
| 2026-09-22 15:59 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | SPJM clamp and live-response jump tests |
| 2026-09-22 15:59 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Seeders/SeederIdempotencyTest.php | Snapshot notes and media |
| 2026-09-22 15:59 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Admin/NotesPanelTest.php | Scope note/log queries to own records |
| 2026-09-22 15:59 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Admin/OperatorRowScopeTest.php | Assert import scoping with seeded data |
| 2026-09-22 15:59 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Admin/ShipmentActivityLoggingTest.php | Pick container without seeded media |
| 2026-09-22 15:59 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Added SPJM clamp and condition-seeder checks |
| 2026-09-22 16:12 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Concerns/ShipmentFields.php | Add footer components after containers repeater |
| 2026-09-22 16:12 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ImportShipments/Schemas/ImportShipmentForm.php | Move loading fields below containers repeater |
| 2026-09-22 16:12 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Loading fields now below the containers repeater |
| 2026-09-22 17:05 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Livewire/Customer/Dashboard.php | Type filter defaults to All |
| 2026-09-22 17:05 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/dashboard.blade.php | Added All option and per-row Type badge |
| 2026-09-22 17:20 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Livewire/Customer/Dashboard.php | Merged export and import into one manually paginated list |
| 2026-09-22 17:20 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/dashboard.blade.php | Per-row detail links resolved from shipment instance |
| 2026-09-22 17:20 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Portal/PortalTest.php | Combined-list default and type filter coverage |
| 2026-09-22 17:40 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/dashboard.blade.php | Redesigned filter bar: full-width search, 5-col filters, count footer |
| 2026-09-22 17:40 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Portal/PortalTest.php | Type-scoped assertions and placeholder-safe checks |
| 2026-09-22 17:50 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Combined B/L list portal UAT checklist |
| 2026-09-22 18:10 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/migrations/2026_09_15_040006_create_import_shipments_table.php | Added actual_arrival_at to import_shipments |
| 2026-09-22 18:10 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Models/ImportShipment.php | Fillable and cast for actual_arrival_at |
| 2026-09-22 18:10 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ImportShipments/Schemas/ImportShipmentForm.php | Actual arrival field on BillingPayment step |
| 2026-09-22 18:10 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ExportShipments/Schemas/ExportShipmentForm.php | Sailing dates: departure and ETA on GateInCy, actual arrival on FinalChecking |
| 2026-09-22 18:10 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Concerns/TableExportColumns.php | Import CSV export includes actual_arrival_at |
| 2026-09-22 18:10 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/seeders/DemoImportShipmentSeeder.php | Completed import seeds actual arrival |
| 2026-09-22 18:10 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/seeders/DemoSpjmShipmentSeeder.php | Completed SPJM import arrives a day late |
| 2026-09-22 18:25 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/dashboard.blade.php | Clickable rows and Document date replaces ETA |
| 2026-09-22 18:35 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Livewire/Customer/Dashboard.php | Status filter limited to In Progress, Completed, Cancelled; per-page defaults to 50 |
| 2026-09-22 18:35 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/dashboard.blade.php | Colored status badges and per-page selector |
| 2026-09-22 18:40 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/dashboard.blade.php | Hidden Containers column |
| 2026-09-22 18:55 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/migrations/2026_09_15_040006_create_import_shipments_table.php | Added shipment_mode to import_shipments |
| 2026-09-22 18:55 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Models/ImportShipment.php | ShipmentMode fillable and cast |
| 2026-09-22 18:55 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ImportShipments/Schemas/ImportShipmentForm.php | Shipment mode after B/L on Checking document |
| 2026-09-22 18:55 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Concerns/TableExportColumns.php | Import CSV export includes shipment_mode |
| 2026-09-22 18:55 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/seeders/DemoImportShipmentSeeder.php | Import demo rows seeded FCL |
| 2026-09-22 18:55 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/seeders/DemoSpjmShipmentSeeder.php | SPJM import demo rows seeded FCL |
| 2026-09-22 18:55 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/seeders/DemoImportConditionSeeder.php | Condition imports seeded FCL, one LCL |
| 2026-09-22 18:55 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/dashboard.blade.php | AJU number and Shipment mode columns, Document created header |
| 2026-09-22 19:05 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/dashboard.blade.php | Reverted AJU and mode columns from table |
| 2026-09-22 19:05 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/partials/shipment-summary.blade.php | Detail header: AJU, mode added; ETA replaced by Document created |
| 2026-09-22 19:15 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/components/shipment-timeline.blade.php | Renamed Journey to Tracking progress, dropped actual/estimate visuals |
| 2026-09-22 19:20 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/partials/shipment-summary.blade.php | Header trimmed to company, type, mode, AJU, container count, doc date |
| 2026-09-22 19:30 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Services/ShipmentTimeline.php | ETA estimate gated on Gate in CY for export, Payment billing for import |
| 2026-09-22 19:45 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Services/ShipmentTimeline.php | Shipment progress lists milestone steps with datetimes from the log trail |
| 2026-09-22 19:45 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Services/ShipmentTimelineEntry.php | Pending flag for upcoming milestone steps |
| 2026-09-22 19:45 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/components/shipment-timeline.blade.php | Pending steps greyed in Tracking progress |
| 2026-09-22 19:45 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Services/ActivityLogger.php | Optional backdated occurred_at on record |
| 2026-09-22 19:45 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/seeders/DemoActivityLogSeeder.php | Day-apart milestone trails for main demo shipments |
| 2026-09-22 19:45 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Portal/PortalTest.php | Latest-journey assertion follows milestone steps |
| 2026-09-22 19:45 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Milestone log count assertion seed-independent |
| 2026-09-23 11:53 | Codex (GPT-6) | UPDATE | resources/views/livewire/customer/dashboard.blade.php | Import badge in the portal Type column is green |
| 2026-09-23 11:59 | Codex (GPT-6) | UPDATE | plans/_progress.md | Started plan for portal tracking step field display |
| 2026-09-23 11:59 | Codex (GPT-6) | UPDATE | app/Services/ShipmentTimelineEntry.php | Added optional labeled values to timeline entries |
| 2026-09-23 11:59 | Codex (GPT-6) | UPDATE | plans/_progress.md | Recorded typed timeline field-value payload completion |
| 2026-09-23 11:59 | Codex (GPT-6) | UPDATE | app/Services/ShipmentTimeline.php | Mapped shipment fields to their Import and Export milestones |
| 2026-09-23 11:59 | Codex (GPT-6) | UPDATE | plans/_progress.md | Completed milestone field mapping and began portal rendering |
| 2026-09-23 12:01 | Codex (GPT-6) | UPDATE | resources/views/components/shipment-timeline.blade.php | Rendered milestone field values in the portal progress list |
| 2026-09-23 12:01 | Codex (GPT-6) | UPDATE | plans/_progress.md | Completed portal timeline rendering and began UAT update |
| 2026-09-23 12:02 | Codex (GPT-6) | UPDATE | docs/UAT.md | Added portal checks for milestone field values |
| 2026-09-23 12:03 | Codex (GPT-6) | UPDATE | app/Services/ShipmentTimelineEntry.php | Documented the timeline field shape on its constructor |
| 2026-09-23 12:03 | Codex (GPT-6) | UPDATE | docs/UAT.md | Uses a portal user assigned to the sample shipments |
| 2026-09-23 12:03 | Codex (GPT-6) | UPDATE | app/Services/ShipmentTimeline.php | Aligned imports and formatted the milestone mapper signature |
| 2026-09-23 12:03 | Codex (GPT-6) | UPDATE | plans/_progress.md | Completed portal tracking step fields plan and checks |
| 2026-09-23 12:06 | Devin (SWE-2 Max) | UPDATE | app/Livewire/Customer/ImportShipmentDetail.php | Revision request now saved as customer-authored shipment note |
| 2026-09-23 12:07 | Codex (GPT-6) | UPDATE | plans/_progress.md | Started responsive right-side timeline fields plan |
| 2026-09-23 12:07 | Codex (GPT-6) | UPDATE | resources/views/components/shipment-timeline.blade.php | Moved milestone fields into a responsive right-hand column |
| 2026-09-23 12:07 | Codex (GPT-6) | UPDATE | resources/views/components/shipment-timeline.blade.php | Kept field labels beside values within the right column |
| 2026-09-23 12:07 | Codex (GPT-6) | UPDATE | plans/_progress.md | Completed right-side timeline layout and began UAT update |
| 2026-09-23 12:07 | Codex (GPT-6) | UPDATE | docs/UAT.md | Added desktop right-column and mobile stacking checks |
| 2026-09-23 12:07 | Codex (GPT-6) | UPDATE | plans/_progress.md | Completed layout UAT changes and began final review |
| 2026-09-23 12:09 | Codex (GPT-6) | UPDATE | plans/_progress.md | Completed responsive timeline fields plan and review |
| 2026-09-23 12:09 | Devin (SWE-2 Max) | UPDATE | app/Livewire/Customer/ImportShipmentDetail.php | Pass customer's own notes to the portal view |
| 2026-09-23 12:09 | Devin (SWE-2 Max) | UPDATE | resources/views/livewire/customer/import-shipment-detail.blade.php | Show "Your messages" list in draft PIB card |
| 2026-09-23 12:09 | Devin (SWE-2 Max) | UPDATE | tests/Feature/Portal/PortalTest.php | Test note storage, portal visibility, internal-only boundary |
| 2026-09-23 12:09 | Devin (SWE-2 Max) | UPDATE | docs/UAT.md | Added revision-notes UAT checklist |
| 2026-09-23 12:11 | Codex (GPT-6) | UPDATE | plans/_progress.md | Started tracking progress border and font refinement |
| 2026-09-23 12:11 | Codex (GPT-6) | UPDATE | resources/views/components/shipment-timeline.blade.php | Removed divider and matched value text size to milestones |
| 2026-09-23 12:11 | Codex (GPT-6) | UPDATE | plans/_progress.md | Completed divider and font adjustment; began UAT revision |
| 2026-09-23 12:11 | Codex (GPT-6) | UPDATE | docs/UAT.md | Added no-divider and font-scale desktop expectations |
| 2026-09-23 12:11 | Codex (GPT-6) | UPDATE | plans/_progress.md | Completed UAT revision and began final review |
| 2026-09-23 12:12 | Codex (GPT-6) | UPDATE | plans/_progress.md | Completed border and typography refinement |
| 2026-09-23 12:20 | Codex (GPT-6) | UPDATE | plans/_progress.md | Started portal container accordion implementation plan |
| 2026-09-23 12:20 | Codex (GPT-6) | UPDATE | app/Livewire/Customer/ImportShipmentDetail.php | Eager-loaded visible photos and HS codes for import containers |
| 2026-09-23 12:20 | Codex (GPT-6) | UPDATE | plans/_progress.md | Marked import container relation loading complete |
| 2026-09-23 12:20 | Codex (GPT-6) | UPDATE | app/Livewire/Customer/ExportShipmentDetail.php | Eager-loaded customer-visible photos for export containers |
| 2026-09-23 12:20 | Codex (GPT-6) | UPDATE | plans/_progress.md | Completed the import/export container eager-loading step |
| 2026-09-23 12:20 | Codex (GPT-6) | UPDATE | plans/_progress.md | Recorded passing portal and PHP syntax checks |
| 2026-09-23 12:35 | Codex (GPT-6) | UPDATE | plans/_progress.md | Started shared portal container accordion step |
| 2026-09-23 12:35 | Codex (GPT-6) | UPDATE | resources/views/livewire/customer/partials/shipment-containers.blade.php | Replaced new-tab container links with expandable sections |
| 2026-09-23 12:35 | Codex (GPT-6) | UPDATE | plans/_progress.md | Marked the shared accordion structure complete |
| 2026-09-23 12:35 | Codex (GPT-6) | UPDATE | resources/views/livewire/customer/import-shipment-detail.blade.php | Removed obsolete import container page route props |
| 2026-09-23 12:35 | Codex (GPT-6) | UPDATE | plans/_progress.md | Marked import accordion include props removed |
| 2026-09-23 12:35 | Codex (GPT-6) | UPDATE | resources/views/livewire/customer/export-shipment-detail.blade.php | Removed obsolete export container page route props |
| 2026-09-23 12:35 | Codex (GPT-6) | UPDATE | plans/_progress.md | Marked both obsolete container route props removed |
| 2026-09-23 12:35 | Codex (GPT-6) | UPDATE | docs/UAT.md | Added portal accordion expand/collapse checks |
| 2026-09-23 12:35 | Codex (GPT-6) | UPDATE | tests/Feature/Portal/PortalTest.php | Test import/export inline containers have no detail-page links |
| 2026-09-23 12:35 | Codex (GPT-6) | UPDATE | plans/_progress.md | Added the accordion feature-test checkpoint |
| 2026-09-23 12:35 | Codex (GPT-6) | UPDATE | plans/_progress.md | Marked accordion UAT and obsolete route-prop work complete |
| 2026-09-23 12:35 | Codex (GPT-6) | UPDATE | plans/_progress.md | Completed accordion interaction step and recorded regression checks |
| 2026-09-23 12:50 | Codex (GPT-6) | UPDATE | plans/_progress.md | Started Import/Export container admin-field mapping |
| 2026-09-23 12:50 | Codex (GPT-6) | UPDATE | resources/views/livewire/customer/partials/shipment-containers.blade.php | Added type-specific admin fields and visible photo galleries |
| 2026-09-23 12:50 | Codex (GPT-6) | UPDATE | plans/_progress.md | Marked Import/Export field and photo mapping complete |
| 2026-09-23 12:50 | Codex (GPT-6) | UPDATE | tests/Feature/Portal/PortalTest.php | Cover type-specific admin fields and customer-visible container photos |
| 2026-09-23 12:50 | Codex (GPT-6) | UPDATE | docs/UAT.md | Added full Import/Export admin-field parity checks for accordions |
| 2026-09-23 12:50 | Codex (GPT-6) | UPDATE | resources/views/livewire/customer/partials/shipment-containers.blade.php | Replaced nested inline PHP directives with safe Blade blocks |
| 2026-09-23 12:50 | Codex (GPT-6) | UPDATE | plans/_progress.md | Completed admin-field mapping and recorded passing portal tests |
| 2026-09-23 12:50 | Codex (GPT-6) | UPDATE | plans/_progress.md | Set next checkpoint to final portal verification |
| 2026-09-23 12:50 | Codex (GPT-6) | UPDATE | plans/_progress.md | Completed the accordion field-parity UAT checklist |
| 2026-09-23 12:50 | Codex (GPT-6) | UPDATE | plans/_progress.md | Added Import/Export container field-parity regression coverage |
| 2026-09-23 12:23 | Devin (SWE-2 Max) | UPDATE | app/Filament/Resources/ImportShipments/Schemas/ImportShipmentForm.php | Customer-note flag under Confirmation checklist |
| 2026-09-23 12:23 | Devin (SWE-2 Max) | UPDATE | resources/views/filament/bill-of-ladings/milestone-stepper.blade.php | Delegated handler jumps to a named form tab |
| 2026-09-23 12:23 | Devin (SWE-2 Max) | UPDATE | resources/views/livewire/notes-panel.blade.php | Customer-authored notes get marker class + tag |
| 2026-09-23 12:23 | Devin (SWE-2 Max) | UPDATE | resources/css/filament/admin/theme.css | Amber styles for customer notes and the flag |
| 2026-09-23 12:23 | Devin (SWE-2 Max) | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Test customer-note flag on import edit page |
| 2026-09-23 12:23 | Devin (SWE-2 Max) | UPDATE | tests/Feature/Admin/NotesPanelTest.php | Test customer marking in notes panel |
| 2026-09-23 12:23 | Devin (SWE-2 Max) | UPDATE | docs/UAT.md | Added flag/color checks to revision-notes section |
| 2026-09-23 12:30 | Devin (SWE-2 Max) | UPDATE | resources/views/livewire/customer/import-shipment-detail.blade.php | Removed "no action is needed" from confirmed message |
| 2026-09-23 12:30 | Devin (SWE-2 Max) | UPDATE | tests/Feature/Portal/PortalTest.php | Updated confirmed-message assertion |
| 2026-09-23 12:41 | Devin (SWE-2 Max) | CREATE | database/migrations/2026_09_23_053832_add_confirmed_by_to_import_shipments_table.php | Add confirmed_by user FK to import_shipments |
| 2026-09-23 12:41 | Devin (SWE-2 Max) | UPDATE | app/Models/ImportShipment.php | Saving hook stamps confirmed_by + confirmedBy relation |
| 2026-09-23 12:41 | Devin (SWE-2 Max) | UPDATE | app/Filament/Resources/ImportShipments/Schemas/ImportShipmentForm.php | Show "Confirmed by {name}" under the toggle |
| 2026-09-23 12:41 | Devin (SWE-2 Max) | UPDATE | tests/Feature/Portal/PortalTest.php | Assert portal confirm stamps the customer |
| 2026-09-23 12:41 | Devin (SWE-2 Max) | UPDATE | tests/Feature/Admin/AdminPanelSmokeTest.php | Assert admin confirm stamps the admin, uncheck clears |
| 2026-09-23 12:41 | Devin (SWE-2 Max) | UPDATE | docs/UAT.md | Added Confirmed-by checklist item |
| 2026-09-23 13:03 | Codex (GPT-6) | UPDATE | plans/_progress.md | Completed container accordion plan after full verification |
| 2026-09-23 13:33 | Devin (SWE-2 Max) | UPDATE | app/Enums/ExportMilestone.php | Added isContainerStep() flag for container milestones |
| 2026-09-23 13:33 | Devin (SWE-2 Max) | UPDATE | app/Enums/ImportMilestone.php | Added isContainerStep() flag for container milestones |
| 2026-09-23 13:33 | Devin (SWE-2 Max) | CREATE | app/Services/ContainerProgress.php | Per-container summary + steps view model |
| 2026-09-23 13:33 | Devin (SWE-2 Max) | UPDATE | app/Services/ShipmentTimelineEntry.php | Field shape gains optional href and wide keys |
| 2026-09-23 13:33 | Devin (SWE-2 Max) | UPDATE | app/Services/ShipmentTimeline.php | Added forContainers() with step fields and derived status |
| 2026-09-23 13:33 | Devin (SWE-2 Max) | UPDATE | app/Livewire/Customer/ExportShipmentDetail.php | Pass containerProgress to the detail view |
| 2026-09-23 13:33 | Devin (SWE-2 Max) | UPDATE | app/Livewire/Customer/ImportShipmentDetail.php | Pass containerProgress to the detail view |
| 2026-09-23 13:33 | Devin (SWE-2 Max) | UPDATE | resources/views/livewire/customer/export-shipment-detail.blade.php | Forward containerProgress into the containers partial |
| 2026-09-23 13:33 | Devin (SWE-2 Max) | UPDATE | resources/views/livewire/customer/import-shipment-detail.blade.php | Forward containerProgress into the containers partial |
| 2026-09-23 13:36 | Devin (SWE-2 Max) | UPDATE | resources/views/livewire/customer/partials/shipment-containers.blade.php | Rebuilt rows into summary card + progress stepper |
| 2026-09-23 13:36 | Devin (SWE-2 Max) | CREATE | resources/views/livewire/customer/partials/container-steps.blade.php | Vertical done/current/upcoming container stepper |
| 2026-09-23 13:40 | Devin (SWE-2 Max) | UPDATE | tests/Feature/Portal/PortalTest.php | Cover container progress steps, statuses and tracking links |
| 2026-09-23 13:40 | Devin (SWE-2 Max) | UPDATE | docs/UAT.md | Rewrote portal container checklist for summary + steps |
| 2026-09-23 17:10 | Devin (SWE-2 Max) | DELETE | app/Livewire/Customer/ExportContainerDetail.php | Removed unused standalone container page component |
| 2026-09-23 17:10 | Devin (SWE-2 Max) | DELETE | app/Livewire/Customer/ImportContainerDetail.php | Removed unused standalone container page component |
| 2026-09-23 17:10 | Devin (SWE-2 Max) | DELETE | resources/views/livewire/customer/export-container-detail.blade.php | Removed unused standalone container page view |
| 2026-09-23 17:10 | Devin (SWE-2 Max) | DELETE | resources/views/livewire/customer/import-container-detail.blade.php | Removed unused standalone container page view |
| 2026-09-23 17:10 | Devin (SWE-2 Max) | DELETE | app/Enums/ShipmentMilestone.php | Removed unused pre-split milestone enum |
| 2026-09-23 17:10 | Devin (SWE-2 Max) | DELETE | app/Enums/ShipmentType.php | Removed unused shipment type enum |
| 2026-09-23 17:10 | Devin (SWE-2 Max) | DELETE | resources/views/welcome.blade.php | Removed unused default welcome view |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | routes/web.php | Removed standalone portal container routes and imports |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | app/Filament/Concerns/TableExportColumns.php | Dropped removed columns and relations from CSV exports |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | app/Filament/Resources/ActivityLogs/Schemas/ActivityLogForm.php | Removed customer-visibility toggle |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | app/Filament/Resources/ActivityLogs/Tables/ActivityLogsTable.php | Removed customer-visibility icon column |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | app/Filament/Resources/ExportContainers/Tables/ExportContainersTable.php | Removed status column and filter |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | app/Filament/Resources/ExportShipments/Tables/ExportShipmentsTable.php | Removed creator/updater/hsCodes eager-loads and columns |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | app/Filament/Resources/HsCodes/Tables/HsCodesTable.php | Removed export-shipments count column |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | app/Filament/Resources/ImportShipments/Tables/ImportShipmentsTable.php | Removed creator/updater eager-loads and columns |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | app/Livewire/Customer/Dashboard.php | POD/Vessel arrival + latest event via forShipment helpers |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | app/Livewire/Customer/ExportShipmentDetail.php | Passes sailing information to the detail view |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | app/Livewire/Customer/ImportShipmentDetail.php | Passes sailing data; dropped visibility flag writes |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | app/Models/ActivityLog.php | Dropped is_customer_visible attribute |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | app/Models/Concerns/ActsAsShipment.php | Removed latest-event stamping and visibility flag |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | app/Models/ExportContainer.php | Dropped status/latest-event/completed/audit attributes |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | app/Models/ExportShipment.php | Dropped hsCodes relation, latest-event and audit columns |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | app/Models/HsCode.php | Removed exportShipments relation |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | app/Models/ImportContainer.php | Dropped latest-event and audit columns |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | app/Models/ImportShipment.php | Dropped latest-event and audit columns |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | app/Services/ActivityLogger.php | Removed visibility flag, latest-event stamping, ignored attrs |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | app/Services/ContainerProgress.php | Added latestReached() and gated chips; removed next() |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | app/Services/ShipmentTimeline.php | Removed container-page methods; added sailing helpers and chips |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | app/Services/ShipmentTimelineEntry.php | Removed location/detail/isActual/sourceEvent properties |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | database/migrations/2026_09_15_040006_create_export_shipments_table.php | Dropped latest-event and audit columns |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | database/migrations/2026_09_15_040006_create_import_shipments_table.php | Dropped latest-event and audit columns |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | database/migrations/2026_09_15_040007_create_hs_codes_table.php | Dropped export_shipment_hs_code pivot |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | database/migrations/2026_09_15_040008_create_export_containers_table.php | Dropped status/latest-event/completed/audit columns |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | database/migrations/2026_09_15_040008_create_import_containers_table.php | Dropped latest-event and audit columns |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | database/migrations/2026_09_15_040017_create_activity_logs_table.php | Dropped is_customer_visible column |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | database/seeders/DemoExportShipmentSeeder.php | Removed locked ETA and container status/completed values |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | database/seeders/DemoExportConditionSeeder.php | Trimmed draft/early/cancelled rows to unlocked fields |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | database/seeders/DemoImportConditionSeeder.php | Trimmed draft/early/cancelled rows to unlocked fields |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | database/seeders/DemoContainerConditionSeeder.php | Dropped export container status values |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | database/seeders/DemoAttachmentSeeder.php | Dropped uploaded_by from container created_by |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | database/seeders/DemoActivityLogSeeder.php | Milestone trails for every non-draft shipment; removed fake history |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | resources/views/components/shipment-timeline.blade.php | Removed location/detail branches |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | resources/views/filament/bill-of-ladings/milestone-stepper.blade.php | Fixed stale ShipmentMilestone docblock reference |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | resources/views/livewire/customer/dashboard.blade.php | POD/Vessel arrival column; renamed document-date header |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | resources/views/livewire/customer/export-shipment-detail.blade.php | Included sailing card after the summary |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | resources/views/livewire/customer/import-shipment-detail.blade.php | Included sailing card after the summary |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | resources/views/livewire/customer/partials/sailing-information.blade.php | Repurposed into milestone-gated sailing card |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | resources/views/livewire/customer/partials/shipment-containers.blade.php | Latest-step row text, gated chips, no last-update line |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | resources/views/livewire/customer/partials/shipment-summary.blade.php | Status pill, completed-at tile, renamed document label |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | tests/Feature/Portal/PortalTest.php | Removed container-route tests; added chips/sailing/summary/dashboard coverage |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | tests/Feature/Admin/ShipmentActivityLoggingTest.php | Dropped latest-event and visibility assertions |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | tests/Feature/Admin/TableExportAndPruneTest.php | Dropped removed-relation export assertions |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | docs/ERD.md | Dropped removed columns and export HS-code pivot |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | README.md | Portal containers inline; removed container routes |
| 2026-09-23 17:49 | Devin (SWE-2 Max) | UPDATE | docs/UAT.md | Rewrote portal section; fixed stale container/journey items |
| 2026-09-23 18:18 | Devin (SWE-2 Max) | UPDATE | database/seeders/DemoActivityLogSeeder.php | Trails skip step 1; seeded dates aligned to step times |
| 2026-09-23 18:18 | Devin (SWE-2 Max) | UPDATE | app/Services/ShipmentTimeline.php | Summary tiles gated on unlocking milestone |
| 2026-09-23 18:18 | Devin (SWE-2 Max) | UPDATE | tests/Feature/Portal/PortalTest.php | Added locked-summary-tiles test |
| 2026-09-23 19:00 | Muse Spark | UPDATE | resources/views/livewire/customer/partials/shipment-summary.blade.php | Unified card shell + Shipment overview header |
| 2026-09-23 19:00 | Muse Spark | UPDATE | resources/views/livewire/customer/partials/sailing-information.blade.php | Unified card shell + header/body padding |
| 2026-09-23 19:00 | Muse Spark | UPDATE | resources/views/components/shipment-timeline.blade.php | Normalized header to shared flex style |
| 2026-09-23 19:00 | Muse Spark | UPDATE | resources/views/livewire/customer/partials/shipment-containers.blade.php | Normalized header padding to shared style |
| 2026-09-23 19:00 | Muse Spark | UPDATE | resources/views/livewire/customer/import-shipment-detail.blade.php | PIB block to shared card shell + header |
| 2026-09-23 19:15 | Muse Spark | UPDATE | resources/views/components/shipment-timeline.blade.php | Vertical line nodes matching container progress |
| 2026-09-23 19:15 | Muse Spark | UPDATE | resources/views/livewire/customer/partials/shipment-summary.blade.php | Pill palette + label typography normalized |
| 2026-09-23 19:15 | Muse Spark | UPDATE | resources/views/livewire/customer/import-shipment-detail.blade.php | PIB body spacing to shared card rhythm |
| 2026-09-23 19:15 | Muse Spark | UPDATE | docs/UAT.md | B/L detail consistency checklist |
| 2026-09-23 19:30 | Muse Spark | UPDATE | resources/views/components/shipment-timeline.blade.php | Fields to right column with divider |
| 2026-09-23 19:35 | Muse Spark | UPDATE | resources/views/livewire/customer/partials/container-steps.blade.php | Fields to right column matching timeline |
| 2026-09-23 19:40 | Muse Spark | UPDATE | resources/views/components/shipment-timeline.blade.php | Latest badge green, blue removed |
| 2026-09-23 19:40 | Muse Spark | UPDATE | resources/views/livewire/customer/partials/container-steps.blade.php | Latest badge green, blue removed |
| 2026-09-23 19:45 | Muse Spark | UPDATE | resources/views/components/shipment-timeline.blade.php | Removed latest box highlight |
| 2026-09-23 19:45 | Muse Spark | UPDATE | resources/views/livewire/customer/partials/container-steps.blade.php | Removed latest box highlight |
| 2026-09-23 20:00 | Muse Spark | UPDATE | resources/views/livewire/customer/partials/shipment-containers.blade.php | Plain table columns, blue links, dividers |
| 2026-09-24 09:36 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/partials/shipment-containers.blade.php | Removed Containers section header; table sits at card top |
| 2026-09-24 09:36 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Checklist for headerless containers table |
| 2026-09-24 09:38 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/partials/shipment-containers.blade.php | Container row shows event name without "Latest:" prefix |
| 2026-09-24 09:38 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Portal/PortalTest.php | Assertions updated for prefix-free container row text |
| 2026-09-24 09:38 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Checklist updated for prefix-free container row text |
| 2026-09-24 09:39 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Services/ShipmentTimeline.php | Dropped seal chip from container row chips |
| 2026-09-24 09:39 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Services/ContainerProgress.php | Chips docblock no longer mentions seal |
| 2026-09-24 09:39 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Portal/PortalTest.php | Removed seal-chip assertions, added chip-absence guard |
| 2026-09-24 09:39 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Checklist updated: seal only in its own column |
| 2026-09-24 09:43 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/partials/shipment-containers.blade.php | Removed Container summary heading and tiles; card keeps Track live + photos |
| 2026-09-24 09:43 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Portal/PortalTest.php | Tile assertions trimmed; summary lock test now service-level |
| 2026-09-24 09:43 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Checklist updated for summary removal |
| 2026-09-24 09:43 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/components/shipment-timeline.blade.php | Dropped left border from milestone fields column |
| 2026-09-24 09:43 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Checklist for divider-free tracking progress |
| 2026-09-24 09:44 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/components/shipment-timeline.blade.php | Removed Upcoming badge and Pending label from milestone steps |
| 2026-09-24 09:44 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Checklist for status-label-free tracking progress |
| 2026-09-24 09:46 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/partials/sailing-information.blade.php | Removed Sailing information header; card starts with route row |
| 2026-09-24 09:46 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/export-shipment-detail.blade.php | Sailing card moved above the containers section |
| 2026-09-24 09:46 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/import-shipment-detail.blade.php | Sailing card moved above the containers section |
| 2026-09-24 09:46 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Portal/PortalTest.php | Sailing header assertions replaced with content anchors |
| 2026-09-24 09:46 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Checklist updated for sailing card placement |
| 2026-09-24 09:47 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/partials/container-steps.blade.php | Dropped left border and padding from step fields column |
| 2026-09-24 09:47 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Divider checklist covers container progress too |
| 2026-09-24 09:49 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/partials/shipment-containers.blade.php | Removed divider before blank chevron header cell |
| 2026-09-24 09:49 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Header-row checklist covers chevron column |
| 2026-09-24 09:51 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/partials/sailing-information.blade.php | Route row gets gradient line with arrow marker |
| 2026-09-24 09:51 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Checklist item for the sailing route track |
| 2026-09-24 09:54 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/partials/sailing-information.blade.php | Route simplified: dashed line, no port cards, right-aligned destination |
| 2026-09-24 09:54 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Route checklist matches dashed-line design |
| 2026-09-24 10:45 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/partials/sailing-information.blade.php | Route strip rebuilt to reference: truck icon, dashed line, end dots, facts below |
| 2026-09-24 10:45 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Route strip checklist rewritten for reference layout |
| 2026-09-24 10:53 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/partials/sailing-information.blade.php | Card shell removed; renders as the card's top strip |
| 2026-09-24 10:53 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/partials/shipment-containers.blade.php | Card shell removed; table renders inside shared card |
| 2026-09-24 10:53 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/export-shipment-detail.blade.php | Sailing strip and containers merged into one card |
| 2026-09-24 10:53 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/import-shipment-detail.blade.php | Sailing strip and containers merged into one card |
| 2026-09-24 10:53 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Checklist updated for the merged sailing + containers card |
| 2026-09-24 10:54 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/partials/container-steps.blade.php | Heading renamed to Cargo tracking; Upcoming badge removed |
| 2026-09-24 10:54 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Portal/PortalTest.php | Assertions updated for the Cargo tracking heading |
| 2026-09-24 10:54 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Checklist renamed to Cargo tracking; Upcoming item updated |
| 2026-09-24 11:00 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/partials/shipment-summary.blade.php | Vessel name, voyage and shipping line added to the overview grid |
| 2026-09-24 11:00 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/partials/sailing-information.blade.php | Vessel facts removed; strip now holds only the route |
| 2026-09-24 11:00 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/export-shipment-detail.blade.php | Sailing values passed into the shipment summary |
| 2026-09-24 11:00 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/import-shipment-detail.blade.php | Sailing values passed into the shipment summary |
| 2026-09-24 11:00 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Checklists updated: vessel facts live in Shipment overview |
| 2026-09-24 11:01 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | resources/views/livewire/customer/partials/shipment-summary.blade.php | B/L number added as the first overview fact |
| 2026-09-24 11:01 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Checklist for B/L number in Shipment overview |
| 2026-09-24 12:48 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ExportShipments/Schemas/ExportShipmentForm.php | Departure date and ETA moved to the Containers tab after AJU number |
| 2026-09-24 12:48 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Checklist updated for the moved export sailing dates |
| 2026-09-24 12:50 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ExportShipments/Schemas/ExportShipmentForm.php | Actual arrival removed from the export form |
| 2026-09-24 12:50 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Checklist updated: Actual arrival removed (export) |
| 2026-09-24 12:52 | human | UPDATE | EXPORT.md | Rewritten to the step-by-step field format |
| 2026-09-24 12:52 | human | UPDATE | IMPORT.md | Rewritten to the step-by-step field format |
| 2026-09-24 13:21 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/migrations/2026_09_15_040006_create_export_shipments_table.php | Dropped actual_arrival_at from export_shipments |
| 2026-09-24 13:21 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Models/ExportShipment.php | Fillable and cast for actual_arrival_at removed |
| 2026-09-24 13:21 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Services/ShipmentTimeline.php | Export Actual arrival field removed |
| 2026-09-24 13:21 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Concerns/TableExportColumns.php | Export CSV list without actual_arrival_at |
| 2026-09-24 13:21 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | database/seeders/DemoExportShipmentSeeder.php | actual_arrival_at removed from the completed export |
| 2026-09-24 13:21 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/ERD.md | Export shipment column list without actual_arrival_at |
| 2026-09-24 13:21 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | EXPORT.md | Actual arrival line dropped from Step 8 |
| 2026-09-24 13:21 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | tests/Feature/Portal/PortalTest.php | Actual-arrival coverage moved to import shipments |
| 2026-09-24 13:21 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Export checklists without the Actual arrival field |
| 2026-09-24 13:25 | Devin (DeepSeek V4.1 Flash Max) | CREATE | app/Filament/Widgets/BillOfLadingsWidget.php | Dashboard shortcuts into the Export and Import lists |
| 2026-09-24 13:25 | Devin (DeepSeek V4.1 Flash Max) | CREATE | resources/views/filament/widgets/bill-of-ladings.blade.php | Widget cards for the two B/L lists |
| 2026-09-24 13:25 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Providers/Filament/AdminPanelProvider.php | Filament info widget removed from the dashboard |
| 2026-09-24 13:25 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Dashboard widget checklist |
| 2026-09-24 13:27 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Resources/ImportShipments/Schemas/ImportShipmentForm.php | Loading fields moved above the containers repeater |
| 2026-09-24 13:27 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Concerns/ShipmentFields.php | containersTab docblock updated |
| 2026-09-24 13:27 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | docs/UAT.md | Import loading-fields checklist updated |
| 2026-09-24 13:34 | Devin (DeepSeek V4.1 Flash Max) | UPDATE | app/Filament/Concerns/ShipmentFields.php | Unused footer slot dropped from containersTab |
| 2026-09-24 13:40 | Muse Spark | UPDATE | database/migrations/2026_09_15_040006_create_import_shipments_table.php | Dropped actual_arrival_at from import_shipments |
| 2026-09-24 13:40 | Muse Spark | UPDATE | app/Models/ImportShipment.php | Fillable and cast for actual_arrival_at removed |
| 2026-09-24 13:40 | Muse Spark | UPDATE | app/Filament/Resources/ImportShipments/Schemas/ImportShipmentForm.php | Actual arrival removed from import form Step 10 |
| 2026-09-24 13:40 | Muse Spark | UPDATE | app/Services/ShipmentTimeline.php | Import Actual arrival field removed |
| 2026-09-24 13:40 | Muse Spark | UPDATE | app/Filament/Concerns/TableExportColumns.php | Import CSV list without actual_arrival_at |
| 2026-09-24 13:40 | Muse Spark | UPDATE | resources/views/livewire/customer/partials/sailing-information.blade.php | Sailing strip ETA only, no actual arrival branch |
| 2026-09-24 13:40 | Muse Spark | UPDATE | resources/views/livewire/customer/dashboard.blade.php | Dashboard POD arrival ETA only |
| 2026-09-24 13:40 | Muse Spark | UPDATE | database/seeders/DemoImportShipmentSeeder.php | actual_arrival_at removed from completed import |
| 2026-09-24 13:40 | Muse Spark | UPDATE | database/seeders/DemoSpjmShipmentSeeder.php | actual_arrival_at removed from SPJM import |
| 2026-09-24 13:40 | Muse Spark | UPDATE | tests/Feature/Portal/PortalTest.php | Sailing and dashboard tests assert ETA only |
| 2026-09-24 13:40 | Muse Spark | UPDATE | IMPORT.md | Actual arrival line dropped from Step 10 |
| 2026-09-24 13:40 | Muse Spark | UPDATE | docs/UAT.md | Import checklists without Actual arrival field |
| 2026-09-24 13:55 | Muse Spark | UPDATE | database/seeders/DemoExportShipmentSeeder.php | Completed export covers do_number, driver license and tracking url |
| 2026-09-24 13:55 | Muse Spark | UPDATE | app/Filament/Resources/ExportContainers/Schemas/ExportContainerForm.php | Fixed stale Stuffing step label and field list |
| 2026-09-24 14:05 | Muse Spark | UPDATE | database/seeders/DemoImportShipmentSeeder.php | Completed import container covers driver, tracking url, factory and depot |
| 2026-09-24 14:05 | Muse Spark | UPDATE | database/seeders/DemoSpjmShipmentSeeder.php | SPJM containers carry cargo and full tracking pair |
| 2026-09-24 14:05 | Muse Spark | UPDATE | docs/ERD.md | IMPORT_SHIPMENTS lists shipment_mode and confirmed_by |
| 2026-09-24 14:15 | Muse Spark | UPDATE | app/Models/Concerns/ActsAsContainer.php | Picked container photos default to customer-visible |
| 2026-09-24 14:15 | Muse Spark | UPDATE | resources/views/livewire/customer/partials/shipment-containers.blade.php | Seal column export-only, hidden on import |
| 2026-09-24 14:25 | Muse Spark | UPDATE | app/Models/Concerns/ActsAsContainer.php | Attachment ownership exclusive across containers and processes |
| 2026-09-24 14:30 | Muse Spark | UPDATE | resources/views/livewire/customer/partials/shipment-containers.blade.php | Track live link moved under tracking position cell |
| 2026-09-24 14:35 | Muse Spark | UPDATE | resources/views/livewire/customer/partials/sailing-information.blade.php | Truck icon removed from sailing strip |
| 2026-09-24 14:35 | Muse Spark | CREATE | database/migrations/2026_09_24_141100_add_seal_number_to_import_containers_table.php | Seal number column on import_containers |
| 2026-09-24 14:35 | Muse Spark | UPDATE | app/Models/ImportContainer.php | seal_number fillable |
| 2026-09-24 14:35 | Muse Spark | UPDATE | app/Filament/Concerns/ContainerFields.php | importSeal group added |
| 2026-09-24 14:35 | Muse Spark | UPDATE | app/Filament/Resources/ImportShipments/Schemas/ImportShipmentForm.php | Seal in import repeater |
| 2026-09-24 14:35 | Muse Spark | UPDATE | app/Filament/Resources/ImportContainers/Schemas/ImportContainerForm.php | Seal in standalone form |
| 2026-09-24 14:35 | Muse Spark | UPDATE | app/Services/ShipmentTimeline.php | Seal number in import summary |
| 2026-09-24 14:35 | Muse Spark | UPDATE | resources/views/livewire/customer/partials/shipment-containers.blade.php | Seal column for both processes |
| 2026-09-24 14:35 | Muse Spark | UPDATE | app/Filament/Concerns/TableExportColumns.php | seal_number in import CSV |
| 2026-09-24 14:35 | Muse Spark | UPDATE | docs/ERD.md | IMPORT_CONTAINERS seal_number |
| 2026-09-24 14:35 | Muse Spark | UPDATE | database/seeders/DemoImportShipmentSeeder.php | Demo seals SL-IMP-0001..0003 |
| 2026-09-24 14:35 | Muse Spark | UPDATE | database/seeders/DemoSpjmShipmentSeeder.php | Demo seals SL-IMP-0010/0011 |
| 2026-09-24 14:35 | Muse Spark | UPDATE | tests/Feature/Portal/PortalTest.php | Import seal parity coverage |
| 2026-09-24 14:45 | Muse Spark | UPDATE | database/seeders/DemoExportShipmentSeeder.php | Second containers on BL-EXP-0002 and BL-EXP-0003 |
| 2026-09-24 14:45 | Muse Spark | UPDATE | database/seeders/DemoImportShipmentSeeder.php | Second container ONEU9988772 on BL-IMP-0002 |
| 2026-09-24 14:45 | Muse Spark | CREATE | database/seeders/DemoImajinerUserSeeder.php | imajiner operator on all companies |
| 2026-09-24 14:45 | Muse Spark | UPDATE | database/seeders/DatabaseSeeder.php | Wired DemoImajinerUserSeeder |
| 2026-09-24 14:45 | Muse Spark | CREATE | database/seeders/BulkShipmentSeeder.php | Standalone 25+25 shipments for pagination |
| 2026-09-24 14:45 | Muse Spark | DELETE | app/Filament/Widgets/BillOfLadingsWidget.php | Dashboard back to Filament default |
| 2026-09-24 14:45 | Muse Spark | DELETE | resources/views/filament/widgets/bill-of-ladings.blade.php | Dashboard back to Filament default |
| 2026-09-24 14:45 | Muse Spark | UPDATE | resources/views/livewire/customer/dashboard.blade.php | B/L column nowrap, B/L-only search labels |
| 2026-09-24 14:45 | Muse Spark | UPDATE | app/Livewire/Customer/Dashboard.php | Portal search matches B/L number only |
| 2026-09-24 14:45 | Muse Spark | UPDATE | tests/Feature/Portal/PortalTest.php | B/L-only search test, imajiner matrix cover |
| 2026-09-24 15:00 | Muse Spark | UPDATE | config/mail.php | mailgun mailer registered |
| 2026-09-24 15:00 | Muse Spark | UPDATE | config/otpz.php | OTPZ_DISABLE_LIMITS flag (non-production only) |
| 2026-09-24 15:00 | Muse Spark | UPDATE | app/Http/Controllers/Customer/LoginController.php | Mail failures show friendly OTP message, logged |
| 2026-09-24 15:00 | Muse Spark | UPDATE | tests/Feature/Portal/PortalTest.php | Mail failure regression test |

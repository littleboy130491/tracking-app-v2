<!--
File: docs/ERD.md
Responsibility: Documents the full database schema as an entity-relationship diagram.
What it does:
- Renders a Mermaid erDiagram covering all domain tables and spatie permission tables.
- Notes below list intentionally excluded Laravel-internal tables and conventions.
How to use: View on GitHub or any Mermaid-compatible renderer.
How to extend: Update the erDiagram block whenever a migration adds or changes a table.
-->

# Entity Relationship Diagram

```mermaid
erDiagram
    USERS ||--o{ OTPS : requests
    USERS ||--o{ COMPANY_USER : handles
    COMPANIES ||--o{ COMPANY_USER : "handled by"
    ROLES ||--o{ MODEL_HAS_ROLES : "assigned via"
    USERS ||..o{ MODEL_HAS_ROLES : "applies to (polymorphic)"
    ROLES ||--o{ ROLE_HAS_PERMISSIONS : has
    PERMISSIONS ||--o{ ROLE_HAS_PERMISSIONS : "granted to"
    PERMISSIONS ||--o{ MODEL_HAS_PERMISSIONS : "assigned via"
    USERS ||..o{ MODEL_HAS_PERMISSIONS : "applies to (polymorphic)"

    COMPANIES ||--o{ BILL_OF_LADINGS : owns
    BILL_OF_LADINGS ||--o{ BILL_OF_LADING_HS_CODE : declares
    HS_CODES ||--o{ BILL_OF_LADING_HS_CODE : "attached via"
    BILL_OF_LADINGS ||--o{ CONTAINERS : contains
    CONTAINERS ||--o{ CONTAINER_LOCATION_UPDATES : reports

    BILL_OF_LADINGS ||--o{ ACTIVITY_LOGS : logs
    CONTAINERS |o--o{ ACTIVITY_LOGS : "logs (optional)"
    USERS |o--o{ ACTIVITY_LOGS : triggers

    BILL_OF_LADINGS |o--o{ CURATOR : attaches
    CONTAINERS |o--o{ CURATOR : attaches
    USERS |o--o{ CURATOR : uploads

    USERS {
        int id PK
        string name
        string email UK
        string phone "nullable"
        string password
        bool is_active
    }

    OTPS {
        uuid id PK
        int user_id FK
        string code
        int attempts
        int status
        bool remember
        string ip_address
    }

    COMPANIES {
        int id PK
        string name
        string code UK
        string email
        string phone
        text address
        bool is_active
    }

    COMPANY_USER {
        int company_id PK, FK
        int user_id PK, FK
    }

    ROLES {
        int id PK
        string name
        string guard_name
        bool is_internal "staff vs portal role"
    }

    PERMISSIONS {
        int id PK
        string name
        string guard_name
    }

    MODEL_HAS_ROLES {
        int role_id PK, FK
        string model_type PK
        int model_id PK "polymorphic, users in practice"
    }

    MODEL_HAS_PERMISSIONS {
        int permission_id PK, FK
        string model_type PK
        int model_id PK "polymorphic, users in practice"
    }

    ROLE_HAS_PERMISSIONS {
        int permission_id PK, FK
        int role_id PK, FK
    }

    BILL_OF_LADINGS {
        int id PK
        string reference_number UK
        string bl_number
        string aju_number
        string do_number
        string shipment_type "export | import"
        int company_id FK
        string company_name_snapshot
        string status "draft | in_progress | completed | cancelled"
        string current_milestone "position in the type's milestone sequence"
        string draft_pib_confirmation_status
        string billing_issuance_status
        string billing_payment_status
        string billing_response "SPPB | AP | SPJK | SPJM"
        string thc_payment_status
        string behandle_payment_status
        date departure_date
        timestamp eta_at
        int created_by FK
        int updated_by FK
        timestamp deleted_at "soft delete"
    }

    HS_CODES {
        int id PK
        string code "unique"
        text description
    }

    BILL_OF_LADING_HS_CODE {
        int id PK
        int bill_of_lading_id FK
        int hs_code_id FK
    }

    CONTAINERS {
        int id PK
        int bill_of_lading_id FK
        string container_number "unique per B/L"
        string size
        string type
        string seal_number
        string status
        string stuffing_status
        string inspection_status
        string factory_loading_status
        decimal gross_weight
        decimal vgm_value
        decimal cbm
        int final_checked_by FK
        int created_by FK
        timestamp deleted_at "soft delete"
    }

    CONTAINER_LOCATION_UPDATES {
        int id PK
        int container_id FK
        string location_name
        decimal latitude
        decimal longitude
        timestamp reported_at
        bool is_customer_visible
        int created_by FK
    }

    ACTIVITY_LOGS {
        int id PK
        int bill_of_lading_id FK
        int container_id FK "nullable"
        int actor_id FK "nullable"
        string event
        string entity_type
        int entity_id
        json old_values
        json new_values
        bool is_customer_visible
        timestamp occurred_at
    }

    CURATOR {
        int id PK
        string name
        string path
        string type
        int bill_of_lading_id FK "nullable"
        int container_id FK "nullable"
        string category
        bool is_customer_visible
        int uploaded_by FK
    }
```

## Notes

- **Excluded (Laravel internals):** `cache`, `jobs`, `sessions`, `password_reset_tokens` — framework plumbing, not domain data.
- **`CURATOR` is the attachments table.** `App\Models\Attachment` extends Curator's media model; the shipment columns (`bill_of_lading_id`, `container_id`, `category`, `is_customer_visible`, `uploaded_by`) were added to it and the old `attachments` table was dropped.
- **Audit FKs:** every `created_by` / `updated_by` / `final_checked_by` / `actor_id` / `uploaded_by` column references `users.id`. Only `ACTIVITY_LOGS` and `CURATOR` draw their user edges; the rest are omitted to keep the diagram readable.
- **Polymorphic pivots:** `model_has_roles` / `model_has_permissions` have no real FK to `users` (`model_type` + `model_id`); dotted lines mark that. In practice only `users` rows appear there.
- **Cardinality:** `|o` on the left means the child's FK is nullable (e.g. an `activity_log` may have no `container_id` when the entry is B/L-scoped).
- **Composite unique keys:** `company_user (company_id, user_id)`, `bill_of_lading_hs_code (bl_id, hs_code_id)`, `containers (bl_id, container_number)`.

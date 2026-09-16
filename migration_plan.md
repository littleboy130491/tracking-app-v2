Berikut rancangan database untuk **B/L → banyak Container**, workflow Export/Import yang bisa dikonfigurasi, serta conditional logic admin panel.

Ini spesifikasi migration yang netral terhadap framework. **Field bisnis disimpan sebagai kolom tetap**, sementara field tambahan buatan user disimpan melalui definisi field dan nilai dinamis.

### 1. Konvensi umum

Semua tabel menggunakan:

| Field        | Tipe      | Ketentuan        |
| ------------ | --------- | ---------------- |
| `id`         | BIGINT    | Primary key      |
| `created_at` | TIMESTAMP | Waktu dibuat     |
| `updated_at` | TIMESTAMP | Waktu diperbarui |

Ketentuan lain:

- Semua FK menggunakan tipe yang sama dengan PK.
- Tanggal operasional menggunakan `DATE`; kejadian dengan jam menggunakan `TIMESTAMP`, disimpan dalam UTC.
- Nomor dokumen, kontainer, dan lisensi menggunakan `VARCHAR`, bukan angka.
- Field operasional boleh `NULL` saat record dibuat; kewajibannya divalidasi ketika tahapan diselesaikan.
- Status menggunakan `VARCHAR` dengan validasi aplikasi agar mudah dikembangkan.
- Record shipment dan riwayat tidak dihapus secara cascade ketika template atau user dihapus.

### 2. `customers`

Identitas perusahaan pemilik shipment.

| Field       | Tipe         | Ketentuan        |
| ----------- | ------------ | ---------------- |
| `name`      | VARCHAR(255) | Wajib            |
| `code`      | VARCHAR(50)  | Nullable, unique |
| `email`     | VARCHAR(255) | Nullable         |
| `phone`     | VARCHAR(50)  | Nullable         |
| `address`   | TEXT         | Nullable         |
| `is_active` | BOOLEAN      | Default true     |

Customer dapat berperan sebagai importir maupun eksportir, bergantung pada tipe shipment.

### 3. User dan role

Gunakan tabel autentikasi yang sudah ada jika tersedia.

**`users`**

| Field         | Tipe           | Ketentuan                     |
| ------------- | -------------- | ----------------------------- |
| `customer_id` | FK → customers | Nullable untuk admin internal |
| `name`        | VARCHAR(255)   | Wajib                         |
| `email`       | VARCHAR(255)   | Unique                        |
| `password`    | VARCHAR(255)   | Hash                          |
| `is_active`   | BOOLEAN        | Default true                  |

**`roles`**

| Field         | Tipe         | Ketentuan                         |
| ------------- | ------------ | --------------------------------- |
| `name`        | VARCHAR(100) | Nama tampilan                     |
| `key`         | VARCHAR(100) | Unique                            |
| `is_internal` | BOOLEAN      | Membedakan role internal/customer |

**`role_user`**

| Field     | Tipe       | Ketentuan |
| --------- | ---------- | --------- |
| `role_id` | FK → roles | Wajib     |
| `user_id` | FK → users | Wajib     |

Unique gabungan: `role_id + user_id`.

### 4. `bill_of_ladings`

Data yang berlaku bersama untuk seluruh kontainer dalam satu B/L.

| Field                           | Tipe                   | Ketentuan                                               |
| ------------------------------- | ---------------------- | ------------------------------------------------------- |
| `reference_number`              | VARCHAR(100)           | Unique; ID shipment internal sebelum nomor B/L tersedia |
| `bl_number`                     | VARCHAR(100)           | Nullable, indexed                                       |
| `shipment_type`                 | VARCHAR(20)            | `export`, `import`                                      |
| `customer_id`                   | FK → customers         | Wajib                                                   |
| `customer_name_snapshot`        | VARCHAR(255)           | Nama importir/eksportir saat shipment dibuat            |
| `workflow_version_id`           | FK → workflow_versions | Wajib; versi yang digunakan shipment                    |
| `aju_number`                    | VARCHAR(100)           | Nullable; satu nilai per B/L                            |
| `do_number`                     | VARCHAR(100)           | Nullable                                                |
| `shipping_line`                 | VARCHAR(255)           | Nullable                                                |
| `vessel_name`                   | VARCHAR(255)           | Nullable                                                |
| `voyage_number`                 | VARCHAR(100)           | Nullable                                                |
| `port_of_loading`               | VARCHAR(255)           | Nullable                                                |
| `port_of_discharge`             | VARCHAR(255)           | Nullable                                                |
| `depot_closing_at`              | TIMESTAMP              | Nullable; terutama Export                               |
| `cy_closing_at`                 | TIMESTAMP              | Nullable; terutama Export                               |
| `departure_date`                | DATE                   | Nullable                                                |
| `eta_at`                        | TIMESTAMP              | Nullable; perkiraan kedatangan                          |
| `actual_arrival_at`             | TIMESTAMP              | Nullable; kedatangan aktual, terpisah dari ETA          |
| `goods_description`             | TEXT                   | Nullable                                                |
| `package_count`                 | INTEGER                | Nullable, ≥ 0                                           |
| `package_unit`                  | VARCHAR(50)            | Nullable; carton, pallet, dll.                          |
| `terminal_name`                 | VARCHAR(255)           | Nullable                                                |
| `loading_date`                  | DATE                   | Nullable                                                |
| `loading_destination`           | TEXT                   | Nullable                                                |
| `draft_pib_confirmation_status` | VARCHAR(30)            | `pending`, `confirmed`, `revision_requested`            |
| `draft_pib_confirmed_at`        | TIMESTAMP              | Nullable                                                |
| `draft_pib_confirmation_notes`  | TEXT                   | Nullable                                                |
| `billing_issuance_status`       | VARCHAR(30)            | `not_issued`, `issued`                                  |
| `billing_issued_at`             | TIMESTAMP              | Nullable                                                |
| `billing_payment_status`        | VARCHAR(30)            | `not_paid`, `processing`, `paid`                        |
| `billing_paid_at`               | TIMESTAMP              | Nullable                                                |
| `billing_response`              | VARCHAR(20)            | Nullable; `SPPB`, `AP`, `SPJK`, `SPJM`, sesuai sumber   |
| `billing_response_at`           | TIMESTAMP              | Nullable                                                |
| `thc_payment_status`            | VARCHAR(30)            | `not_paid`, `processing`, `paid`                        |
| `thc_paid_at`                   | TIMESTAMP              | Nullable                                                |
| `behandle_payment_status`       | VARCHAR(30)            | Nullable; `not_paid`, `processing`, `paid`              |
| `behandle_paid_at`              | TIMESTAMP              | Nullable                                                |
| `do_released_at`                | TIMESTAMP              | Nullable                                                |
| `status`                        | VARCHAR(30)            | `draft`, `in_progress`, `completed`, `cancelled`        |
| `completed_at`                  | TIMESTAMP              | Nullable                                                |
| `created_by`                    | FK → users             | Nullable jika user sudah tidak tersedia                 |
| `updated_by`                    | FK → users             | Nullable                                                |
| `deleted_at`                    | TIMESTAMP              | Soft delete                                             |

**HS code:** saya sarankan tabel terpisah agar satu B/L tidak dibatasi menjadi satu kode.

**`bill_of_lading_hs_codes`**

| Field               | Tipe                 | Ketentuan |
| ------------------- | -------------------- | --------- |
| `bill_of_lading_id` | FK → bill_of_ladings | Wajib     |
| `hs_code`           | VARCHAR(30)          | Wajib     |
| `description`       | TEXT                 | Nullable  |

Unique gabungan: `bill_of_lading_id + hs_code`.

Nomor B/L diberi index, tetapi belum diasumsikan unik global. Nomor AJU dapat diberi unique constraint jika aturan bisnis memastikan satu nomor AJU tidak dipakai oleh B/L lainnya.

### 5. `containers`

Setiap baris adalah **kontainer dalam shipment tertentu**, bukan master aset kontainer.

| Field                         | Tipe                 | Ketentuan                                          |
| ----------------------------- | -------------------- | -------------------------------------------------- |
| `bill_of_lading_id`           | FK → bill_of_ladings | Wajib                                              |
| `container_number`            | VARCHAR(30)          | Wajib                                              |
| `size`                        | VARCHAR(20)          | Nullable; misalnya 20, 40                          |
| `type`                        | VARCHAR(30)          | Nullable; misalnya GP, HC, RF                      |
| `seal_number`                 | VARCHAR(100)         | Nullable                                           |
| `pickup_depot_name`           | VARCHAR(255)         | Nullable                                           |
| `empty_picked_up_at`          | TIMESTAMP            | Nullable                                           |
| `stuffing_date`               | DATE                 | Nullable                                           |
| `stuffing_destination`        | TEXT                 | Nullable                                           |
| `stuffing_status`             | VARCHAR(30)          | `not_started`, `on_process`, `finished`            |
| `stuffing_started_at`         | TIMESTAMP            | Nullable                                           |
| `stuffing_finished_at`        | TIMESTAMP            | Nullable                                           |
| `driver_name`                 | VARCHAR(255)         | Nullable                                           |
| `license_number`              | VARCHAR(100)         | Nullable; arti “No. License” perlu dipastikan      |
| `gross_weight`                | DECIMAL(15,3)        | Nullable, ≥ 0                                      |
| `gross_weight_unit`           | VARCHAR(20)          | Nullable                                           |
| `cbm`                         | DECIMAL(15,3)        | Nullable, ≥ 0                                      |
| `vgm_value`                   | DECIMAL(15,3)        | Nullable, ≥ 0; selalu dalam kg                     |
| `tracking_position`           | VARCHAR(255)         | Nullable; posisi terakhir sebagai teks             |
| `gate_in_port_name`           | VARCHAR(255)         | Nullable                                           |
| `gate_in_cy_at`               | TIMESTAMP            | Nullable                                           |
| `gate_out_cy_at`              | TIMESTAMP            | Nullable                                           |
| `inspection_status`           | VARCHAR(30)          | `not_started`, `in_progress`, `completed`          |
| `inspected_at`                | TIMESTAMP            | Nullable                                           |
| `inspection_notes`            | TEXT                 | Nullable                                           |
| `factory_arrived_at`          | TIMESTAMP            | Nullable                                           |
| `factory_loading_status`      | VARCHAR(30)          | `not_started`, `on_process`, `final_process`       |
| `factory_loading_started_at`  | TIMESTAMP            | Nullable                                           |
| `factory_loading_finished_at` | TIMESTAMP            | Nullable                                           |
| `final_checked`               | BOOLEAN              | Default false                                      |
| `final_checked_at`            | TIMESTAMP            | Nullable                                           |
| `return_depot_name`           | VARCHAR(255)         | Nullable                                           |
| `empty_returned_at`           | TIMESTAMP            | Nullable                                           |
| `status`                      | VARCHAR(30)          | `pending`, `in_progress`, `completed`, `cancelled` |
| `completed_at`                | TIMESTAMP            | Nullable                                           |
| `created_by`                  | FK → users           | Nullable                                           |
| `updated_by`                  | FK → users           | Nullable                                           |
| `deleted_at`                  | TIMESTAMP            | Soft delete                                        |

Unique gabungan: `bill_of_lading_id + container_number`.

Nomor kontainer tidak dibuat unik global karena kontainer yang sama dapat digunakan lagi pada shipment lain.

### 6. Definisi workflow

**`workflow_templates`**

| Field           | Tipe         | Ketentuan                |
| --------------- | ------------ | ------------------------ |
| `name`          | VARCHAR(255) | Misalnya Import Standard |
| `code`          | VARCHAR(100) | Unique                   |
| `shipment_type` | VARCHAR(20)  | `export`, `import`       |
| `description`   | TEXT         | Nullable                 |
| `is_active`     | BOOLEAN      | Default true             |
| `created_by`    | FK → users   | Nullable                 |

**`workflow_versions`**

| Field                  | Tipe                    | Ketentuan                       |
| ---------------------- | ----------------------- | ------------------------------- |
| `workflow_template_id` | FK → workflow_templates | Wajib                           |
| `version_number`       | INTEGER                 | Wajib                           |
| `status`               | VARCHAR(20)             | `draft`, `published`, `retired` |
| `published_at`         | TIMESTAMP               | Nullable                        |
| `published_by`         | FK → users              | Nullable                        |
| `created_by`           | FK → users              | Nullable                        |

Unique gabungan: `workflow_template_id + version_number`.

**`workflow_stages`**

| Field                  | Tipe                   | Ketentuan                                |
| ---------------------- | ---------------------- | ---------------------------------------- |
| `workflow_version_id`  | FK → workflow_versions | Wajib                                    |
| `code`                 | VARCHAR(100)           | Identitas stabil dalam versi             |
| `name`                 | VARCHAR(255)           | Nama tahapan                             |
| `phase`                | VARCHAR(30)            | `output`, `input`, `final`               |
| `scope`                | VARCHAR(20)            | `bill_of_lading`, `container`            |
| `display_order`        | INTEGER                | Urutan tampilan, bukan aturan dependency |
| `instructions`         | TEXT                   | Nullable; petunjuk untuk admin           |
| `is_required`          | BOOLEAN                | Default true                             |
| `is_customer_visible`  | BOOLEAN                | Default false                            |
| `activation_condition` | JSON                   | Nullable; kondisi aktivasi               |
| `activation_mode`      | VARCHAR(30)            | `always`, `once_when_matched`            |
| `created_by`           | FK → users             | Nullable                                 |

Unique gabungan: `workflow_version_id + code`.

Untuk tahap campuran seperti jadwal, **scope tahap dapat tetap B/L**, tetapi form menampilkan field B/L sekaligus repeater field kontainer.

**`workflow_stage_dependencies`**

| Field                 | Tipe                 | Ketentuan                                           |
| --------------------- | -------------------- | --------------------------------------------------- |
| `stage_id`            | FK → workflow_stages | Tahap yang menunggu                                 |
| `depends_on_stage_id` | FK → workflow_stages | Tahap prasyarat                                     |
| `target_match`        | VARCHAR(40)          | `same_bl`, `same_container`, `all_containers_in_bl` |
| `condition`           | JSON                 | Nullable; kapan dependency berlaku                  |

Unique gabungan: `stage_id + depends_on_stage_id + target_match`.

Dependency bersyarat sudah cukup untuk kebutuhan SPJM ini; tidak perlu tabel transition terpisah jika alurnya ditentukan oleh dependency dan kondisi aktivasi.

### 7. Field dan permission workflow

**`workflow_stage_fields`**

| Field                  | Tipe                 | Ketentuan                                                             |
| ---------------------- | -------------------- | --------------------------------------------------------------------- |
| `workflow_stage_id`    | FK → workflow_stages | Wajib                                                                 |
| `key`                  | VARCHAR(100)         | Identitas field                                                       |
| `label`                | VARCHAR(255)         | Label form                                                            |
| `field_type`           | VARCHAR(30)          | text, textarea, number, date, datetime, select, checklist, file, dll. |
| `target_entity`        | VARCHAR(20)          | `bill_of_lading`, `container`                                         |
| `storage_type`         | VARCHAR(20)          | `core_column`, `dynamic`, `attachment`                                |
| `column_name`          | VARCHAR(100)         | Nullable; hanya untuk field inti yang diizinkan                       |
| `options`              | JSON                 | Nullable; pilihan select/checklist                                    |
| `validation_rules`     | JSON                 | Nullable                                                              |
| `default_value`        | JSON                 | Nullable                                                              |
| `is_required`          | BOOLEAN              | Default false                                                         |
| `required_condition`   | JSON                 | Nullable                                                              |
| `visibility_condition` | JSON                 | Nullable                                                              |
| `is_customer_visible`  | BOOLEAN              | Default false                                                         |
| `display_order`        | INTEGER              | Urutan field                                                          |
| `help_text`            | TEXT                 | Nullable                                                              |

Unique gabungan: `workflow_stage_id + key`.

**`workflow_stage_permissions`**

| Field               | Tipe                 | Ketentuan     |
| ------------------- | -------------------- | ------------- |
| `workflow_stage_id` | FK → workflow_stages | Wajib         |
| `role_id`           | FK → roles           | Wajib         |
| `can_view`          | BOOLEAN              | Default false |
| `can_edit`          | BOOLEAN              | Default false |
| `can_complete`      | BOOLEAN              | Default false |
| `can_reopen`        | BOOLEAN              | Default false |
| `can_publish`       | BOOLEAN              | Default false |

Unique gabungan: `workflow_stage_id + role_id`.

### 8. Pelaksanaan workflow

**`stage_records`**

| Field                 | Tipe                 | Ketentuan                                                    |
| --------------------- | -------------------- | ------------------------------------------------------------ |
| `workflow_stage_id`   | FK → workflow_stages | Wajib                                                        |
| `bill_of_lading_id`   | FK → bill_of_ladings | Selalu diisi                                                 |
| `container_id`        | FK → containers      | Nullable untuk tahap level B/L                               |
| `status`              | VARCHAR(30)          | `inactive`, `pending`, `in_progress`, `completed`, `skipped` |
| `activated_at`        | TIMESTAMP            | Nullable                                                     |
| `activation_snapshot` | JSON                 | Nilai pemicu saat tahap aktif, misalnya SPJM                 |
| `started_at`          | TIMESTAMP            | Nullable                                                     |
| `completed_at`        | TIMESTAMP            | Nullable                                                     |
| `completed_by`        | FK → users           | Nullable                                                     |
| `reopened_at`         | TIMESTAMP            | Nullable                                                     |
| `reopened_by`         | FK → users           | Nullable                                                     |
| `reopen_reason`       | TEXT                 | Nullable                                                     |
| `skip_reason`         | TEXT                 | Nullable                                                     |
| `internal_notes`      | TEXT                 | Nullable                                                     |
| `customer_notes`      | TEXT                 | Nullable                                                     |
| `published_at`        | TIMESTAMP            | Nullable                                                     |
| `published_by`        | FK → users           | Nullable                                                     |
| `updated_by`          | FK → users           | Nullable                                                     |
| `lock_version`        | INTEGER              | Default 0; mencegah overwrite bersamaan                      |

Harus unik:

- Tahap B/L: `workflow_stage_id + bill_of_lading_id`, ketika `container_id IS NULL`.
- Tahap Container: `workflow_stage_id + container_id`, ketika `container_id IS NOT NULL`.

Implementasi unique index dengan nilai `NULL` perlu disesuaikan dengan database yang digunakan.

**`stage_field_values`**

Hanya untuk field dinamis; field inti tetap dibaca dari B/L atau Container.

| Field                     | Tipe                       | Ketentuan                                           |
| ------------------------- | -------------------------- | --------------------------------------------------- |
| `stage_record_id`         | FK → stage_records         | Wajib                                               |
| `workflow_stage_field_id` | FK → workflow_stage_fields | Wajib                                               |
| `container_id`            | FK → containers            | Nullable; untuk field per kontainer dalam tahap B/L |
| `value`                   | JSON                       | Nilai sesuai definisi field                         |
| `updated_by`              | FK → users                 | Nullable                                            |

Terapkan uniqueness satu nilai per kombinasi record, definisi field, dan target kontainer, termasuk aturan khusus untuk target B/L yang bernilai `NULL`.

### 9. Dokumen dan audit

**`attachments`**

| Field                     | Tipe                       | Ketentuan                                                           |
| ------------------------- | -------------------------- | ------------------------------------------------------------------- |
| `bill_of_lading_id`       | FK → bill_of_ladings       | Wajib                                                               |
| `container_id`            | FK → containers            | Nullable                                                            |
| `stage_record_id`         | FK → stage_records         | Nullable                                                            |
| `workflow_stage_field_id` | FK → workflow_stage_fields | Nullable                                                            |
| `category`                | VARCHAR(50)                | door_photo, floor_photo, eir_photo, seal_photo, supporting_document |
| `original_filename`       | VARCHAR(255)               | Wajib                                                               |
| `storage_disk`            | VARCHAR(50)                | Wajib                                                               |
| `storage_path`            | TEXT                       | Wajib                                                               |
| `mime_type`               | VARCHAR(100)               | Wajib                                                               |
| `size_bytes`              | BIGINT                     | Wajib                                                               |
| `is_customer_visible`     | BOOLEAN                    | Default false                                                       |
| `uploaded_by`             | FK → users                 | Nullable                                                            |
| `deleted_at`              | TIMESTAMP                  | Soft delete                                                         |

**`activity_logs`**

| Field                 | Tipe                 | Ketentuan                                                     |
| --------------------- | -------------------- | ------------------------------------------------------------- |
| `bill_of_lading_id`   | FK → bill_of_ladings | Wajib                                                         |
| `container_id`        | FK → containers      | Nullable                                                      |
| `stage_record_id`     | FK → stage_records   | Nullable                                                      |
| `actor_id`            | FK → users           | Nullable untuk proses sistem                                  |
| `event`               | VARCHAR(100)         | created, updated, completed, reopened, response_changed, dll. |
| `entity_type`         | VARCHAR(100)         | Record yang berubah                                           |
| `entity_id`           | BIGINT               | ID record                                                     |
| `old_values`          | JSON                 | Nullable                                                      |
| `new_values`          | JSON                 | Nullable                                                      |
| `customer_summary`    | TEXT                 | Ringkasan yang aman ditampilkan                               |
| `is_customer_visible` | BOOLEAN              | Default false                                                 |
| `occurred_at`         | TIMESTAMP            | Wajib                                                         |

Log bersifat append-only. Customer melihat ringkasan yang dipublikasikan, bukan seluruh payload internal.

### 10. Conditional logic admin panel

| Kondisi                      | Perilaku admin panel                                                      |
| ---------------------------- | ------------------------------------------------------------------------- |
| Membuat shipment             | Pilih Export/Import, customer, lalu workflow yang sesuai                  |
| Workflow dipilih             | Gunakan versi published; simpan versi tersebut pada B/L                   |
| Nomor B/L belum tersedia     | Shipment bisa disimpan sebagai draft menggunakan reference internal       |
| Kontainer ditambahkan        | Buat record tahapan scope Container sesuai versi workflow B/L             |
| Tahap scope B/L              | Form mengubah data bersama pada B/L                                       |
| Tahap berisi field Container | Tampilkan repeater/pemilih kontainer; simpan ke kontainer terkait         |
| Tahap scope Container        | Admin harus memilih kontainer; progress kontainer lain tidak ikut berubah |
| Dependency belum selesai     | Tahap terkunci dan tampilkan prasyarat yang belum terpenuhi               |
| Admin menyimpan draft tahap  | Field wajib boleh belum lengkap                                           |
| Admin menyelesaikan tahap    | Validasi required field, dokumen, role, scope, dan dependency             |
| Tahap selesai                | Form terkunci; perubahan memerlukan hak reopen                            |
| Data diubah                  | Simpan perubahan dan activity log dalam transaksi yang sama               |
| Record diedit bersamaan      | Tolak penyimpanan versi lama melalui `lock_version`                       |
| Customer membuka shipment    | Batasi ke `customer_id` miliknya dan field/tahap yang dipublikasikan      |

Semua aturan permission dan dependency dijalankan di backend, bukan hanya melalui tombol yang disembunyikan.

### 11. Conditional logic SPJM

**Pemicu:**

```text
bill_of_lading.shipment_type = import
AND bill_of_lading.billing_response = SPJM
```

**Aksi sistem:**

```text
Aktifkan:
1. Upload All Document                       [B/L]
2. Waiting Process Behandle                  [B/L]
3. Payment Behandle                          [B/L]
4. Container Inspection                      [per Container]
5. Waiting Change Status of SPJM to SPPB      [B/L]
```

Usulan dependency:

| Tahap                       | Prasyarat                                |
| --------------------------- | ---------------------------------------- |
| Upload All Document         | Response Billing selesai dengan SPJM     |
| Waiting Process Behandle    | Upload All Document selesai              |
| Payment Behandle            | Waiting Process Behandle selesai         |
| Container Inspection        | Payment Behandle selesai                 |
| Waiting Change SPJM to SPPB | Inspeksi seluruh kontainer aktif selesai |
| Container Shipping Schedule | Tahap menunggu perubahan ke SPPB selesai |

Saat respons berubah menjadi SPPB:

- Simpan respons terbaru dan waktu perubahannya.
- Pertahankan `activation_snapshot` SPJM.
- Jangan nonaktifkan atau hapus tahapan SPJM yang sudah aktif.
- Tahap “Waiting Change SPJM to SPPB” baru dapat diselesaikan jika respons terbaru SPPB dan dependency terpenuhi.

**AP dan SPJK:** tampilkan status “alur belum dikonfigurasi” dan jangan lanjut otomatis.

**SPPB langsung:** jalur menuju Container Shipping Schedule masih merupakan usulan yang perlu dijadikan aturan workflow setelah disepakati.

### 12. Field umum tidak boleh bergantung pada SPJM

Pada sumber Import, beberapa field umum sejajar dengan tahapan tambahan SPJM. Field tersebut tetap dibutuhkan meskipun responsnya bukan SPJM:

| Field                | Lokasi form yang disarankan |
| -------------------- | --------------------------- |
| Size/type            | Form Container              |
| Description of goods | Detail B/L                  |
| HS codes             | Detail B/L                  |
| Gross weight         | Form Container              |
| Packages             | Detail B/L                  |
| CBM                  | Form Container              |

Tahapan dapat menampilkan field ini untuk diperiksa atau diperbarui, tetapi **jangan menjadikan blok SPJM sebagai satu-satunya tempat pengisiannya**.

### 13. Penyelesaian shipment

Untuk Import, usulan aturannya:

```text
Container selesai:
Empty Container Returned selesai
+ nama depot tersedia
+ tanggal pengembalian tersedia

B/L selesai:
Memiliki minimal satu kontainer aktif
+ seluruh tahap wajib B/L yang berlaku selesai
+ seluruh kontainer aktif selesai
```

Tahap kondisional yang tidak pernah aktif tidak dihitung sebagai pekerjaan tertunda. Jika kontainer baru ditambahkan setelah B/L selesai, sistem harus meminta tindakan reopen dengan role yang berwenang.

**Dua label yang belum boleh diasumsikan dalam implementasi:** arti `No. License` dan apakah closing time/depot/lokasi tertentu selalu sama untuk seluruh kontainer. Struktur di atas mempertahankan `No. License` sebagai teks dan mengikuti pembagian B/L–Container yang sudah kita bahas.

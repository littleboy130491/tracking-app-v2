<!--
File: RECOMMENDATION_PLAN.md
Responsibility: Records advisory findings and optional future improvements for the import workflow.
What it does:
- Assesses the approved admin and customer import experience without changing it.
- Defines no-code operating rules and prioritized future options.
How to use: Treat this as discussion material for operations and future client reviews.
How to extend: Record an approved decision before converting any recommendation into implementation work.
-->

# Import Process Recommendation Plan

| Item | Value |
| :--- | :--- |
| Review date | 2026-09-21 |
| Scope | Import shipment administration and customer tracking |
| Status | Advisory only |
| Approved baseline | Remains unchanged |
| Code changes from this review | None |

## 1. Executive conclusion

The approved import workflow is suitable as a Version 1 internal operator checklist. Its three broad phases are logical:

1. Document intake.
2. PIB, customs, billing, and release processing.
3. Container delivery and empty return.

The workflow should currently be understood as a **manual progress tracker**, not a fully enforced customs state machine. Operators declare progress, most milestone fields remain optional, and one shipment-level milestone controls every container.

The current implementation can remain unchanged. The recommendations below are for operating guidance and possible future client-approved work.

The two most important cautions are:

- AP, SPJK, SPJM, and SPPB are different customs outcomes and should not be treated as equivalent release states.
- A shipment-level delivery milestone can overstate progress when its containers move at different times.

## 2. Current workflow summary

### 2.1 Standard route — 16 milestones

1. Document received.
2. Checking document.
3. Draft PIB.
4. Checking Draft PIB to importer.
5. Waiting confirmation from customer.
6. Final PIB submission and billing issuance.
7. THC payment processing.
8. Waiting for DO release.
9. DO release.
10. Customs billing payment.
11. Customs billing response.
12. Container delivery scheduling.
13. Gate out from inbound terminal.
14. Container en route to factory.
15. Container arrived at factory.
16. Empty container returned.

### 2.2 SPJM route — 21 milestones

When the response is SPJM, five additional stages are inserted after the billing response:

1. Upload all documents.
2. Waiting for behandle processing.
3. Behandle payment.
4. Container inspection.
5. Waiting for SPJM to become SPPB.

After those stages, the workflow returns to delivery scheduling and the four delivery/return milestones.

### 2.3 Existing system behavior

- Operators may advance only one milestone at a time.
- Operators may return to any earlier reached milestone.
- Leaving the first milestone changes a draft shipment to In Progress and publishes it to the customer portal.
- Reaching Empty container returned completes the shipment automatically.
- Milestone changes are written to the activity log and shown to customers.
- Shipment fields and nested container fields unlock according to the current shipment milestone.
- The standalone import-container form exposes the same container fields without milestone locking.

Primary implementation references:

- `IMPORT.md`
- `app/Enums/ImportMilestone.php`
- `app/Models/Concerns/ActsAsShipment.php`
- `app/Filament/Resources/ImportShipments/Schemas/ImportShipmentForm.php`
- `app/Filament/Resources/ImportContainers/Schemas/ImportContainerForm.php`

## 3. What works well

- The three process phases are easy for operators to understand.
- The conditional SPJM path avoids forcing physical-inspection steps onto every shipment.
- Locked fields tell operators which milestone makes them available.
- Newly unlocked empty fields are visually highlighted.
- One-step forward movement and confirmation dialogs reduce accidental progress jumps.
- Regressing progress supports operational corrections.
- Shipment, container, HS-code, note, and attachment changes have audit coverage.
- Company scoping prevents operators and customers from opening unrelated shipments.
- Draft shipments stay internal until work begins.
- Customers can search by B/L or container and inspect shipment/container details.
- Customers can confirm a Draft PIB or request a revision.

## 4. Findings and risks

### 4.1 Progress is declared, not validated

Most milestone-specific fields are optional. An operator can advance while the B/L number, ETA, billing response, return date, or other stage information is empty.

This is acceptable when the milestone means “the operator confirms this happened.” It is not equivalent to the system proving that all required data or evidence exists.

### 4.2 Save and Advance are independent actions

The header exposes separate Save and Advance actions. Advance changes the milestone but does not save unsaved form values.

Operational risk: an operator can enter data, click Advance, and assume both actions were persisted.

Recommended operating sequence: **enter data → Save → Advance**.

### 4.3 Customs response meanings need an explicit rule

| Response | Operational meaning | Release state |
| :--- | :--- | :--- |
| AP | Analyzing Point, commonly related to permit/restriction analysis | Still processing |
| SPJK | Yellow-channel notification and document review | Still processing |
| SPJM | Red-channel notification requiring physical-inspection handling | Still processing |
| SPPB | Approval to release goods from the customs area | Customs cleared |

The current sequence adds extra stages only for SPJM. AP and SPJK otherwise have the same next milestone as SPPB: Container shipping schedule.

This does not force an operator to advance, but the stepper permits it. Operations should not treat AP, SPJK, or SPJM as release approval.

A second clarification is required: does `billing_response` represent the **initial channel classification** or the **latest customs result**? The existing SPJM route behaves most consistently when SPJM remains the initial classification, while “Waiting change status SPJM to SPPB” is tracked as a milestone.

### 4.4 Several fields are unlocked at unrelated stages

| Field | Current unlock point | Observation |
| :--- | :--- | :--- |
| Shipping line | Draft PIB | Commonly known from shipping documents earlier |
| Vessel name | Draft PIB checking | Commonly known earlier |
| Voyage | Final PIB submission | Commonly known earlier |
| Port of loading | THC payment | Not produced by THC payment |
| Departure date | Waiting for DO release | Not produced by DO processing |
| Port of discharge | DO release | Not produced by DO release |
| ETA | Customs billing payment | Not produced by customs payment |
| Goods description, Packages, HS codes | Response billing (Step 11) | Cargo data is commonly available during PIB preparation; container-level copies default from the shipment and can be overridden |
| Container size | Response billing (Step 11) | Not produced by the billing response, but grouped with the container registration |
| Terminal, loading date, and destination | Container shipping schedule | Relevant to delivery planning, but “loading” terminology should be confirmed |

The form appears to use later milestones to spread data entry over time. This may match the client’s working method, but it is not a strict dependency between the stage and the field.

Official Bea Cukai guidance describes the PIB as being prepared from supporting documents and containing tariff/HS classification and goods description. Therefore, goods description and HS code are the strongest candidates for earlier capture if the client later revisits field timing.

### 4.5 SPJM completion is not an explicit SPPB gate

The branch ends with Waiting change status SPJM to SPPB, then continues to delivery scheduling. There is no separate required SPPB-issued milestone, issue date, or release document.

The process can therefore record that staff waited for SPPB without recording the actual clearance evidence.

### 4.6 Multiple containers share one delivery milestone

One shipment milestone unlocks fields for every nested container. Containers can nevertheless gate out, travel, arrive, unload, and return at different times.

Potential effects:

- One advanced container can make the shipment appear advanced.
- Future fields unlock for containers that have not reached that stage.
- Reaching the final shipment milestone completes the shipment even when another container has no empty-return date.

The safest current interpretation is: a shipment reaches a delivery milestone only when **all active, non-cancelled containers** have reached it.

### 4.7 Standalone container editing bypasses milestone guidance

The shipment’s Containers tab follows the milestone gates. The standalone path at **Containers → Import** exposes all fields freely.

This is useful for corrections and administrative recovery. If operators use it for routine work, however, it bypasses the intended step-by-step order.

### 4.8 Container status is separate from container events

The import container has Pending, In Progress, Completed, and Cancelled statuses. Those values are manually edited on the standalone container form; gate-out and empty-return dates do not automatically change them.

A container can therefore display Pending after gate-out, or remain In Progress after its empty return, unless staff update the status separately.

### 4.9 Customer progress is detailed but not immediate

The portal provides shipment facts, containers, sailing information, and a chronological journey. However:

- The main status is only Draft, In Progress, Completed, or Cancelled.
- There is no prominent plain-language current stage.
- Customers receive the internal milestone labels in their journey.
- A future ETA can be sorted after actual events and appear as the Latest event.
- The container tracking URL is recorded but not yet displayed to customers.
- Shipment-level milestone logs can imply that every container reached the same point.

The portal currently answers “What has been recorded?” better than “Where is my shipment now, and what happens next?”

### 4.10 Draft PIB confirmation is not milestone-gated

The confirmation controls appear on every visible, unconfirmed import shipment. They are not restricted to Waiting confirmation from customer.

The page also has no Draft PIB document or download link. If the document is delivered by email or WhatsApp, the portal should eventually state that clearly. Otherwise, a customer may be offered confirmation before seeing the draft.

### 4.11 Internal event names can appear as container updates

Customer journey entries correctly filter activity logs by `is_customer_visible`. The shipment’s container table instead reads denormalized `latest_event` values.

Because every recorded event updates that field, customers can see generic internal labels such as Container Updated or Note Created. The private content is not exposed, but the label may be confusing.

### 4.12 Events with limited supporting data

The following milestones rely mostly on the milestone-change timestamp rather than dedicated operational evidence:

- Upload all documents: no shipment-document picker is shown.
- DO release: no release number or release timestamp.
- Billing payment: no payment date or reference.
- Behandle payment: no payment date or reference.
- Container inspection: no inspection date, result, or notes.
- Delivery scheduling: loading date and destination are recorded, but no scheduled time or per-container appointment.
- SPPB: no issued date or document.

This may be sufficient for a simple progress tracker, but it limits reporting and delay analysis.

## 5. No-code operating procedure for the approved version

These rules can be adopted without changing application behavior:

1. **Always Save before Advance.**
2. Advance only when staff have external evidence that the operational event occurred.
3. Treat AP, SPJK, and SPJM as still processing; do not use them as release authorization.
4. Verify SPPB outside the stepper before delivery scheduling.
5. Decide whether Billing response records the initial channel or the latest response and use it consistently.
6. Offer Draft PIB confirmation only after the customer has received the actual draft.
7. Use the shipment form for routine container updates.
8. Reserve standalone container forms for correction or recovery work.
9. For delivery milestones, require every active non-cancelled container to reach the stage before advancing the shipment.
10. Before completion, verify every active container has an empty-return date and Completed status.
11. Record delays, exceptions, and external evidence in the agreed Notes convention.
12. Supervisors should periodically compare Completed shipments with their container return data.

## 6. Optional future admin model

If the client later requests a more operationally accurate workflow, separate the process into three coordinated tracks.

### 6.1 Track A — Documents and Customs

1. Documents received.
2. Documents checked; missing items identified.
3. Draft PIB prepared.
4. Draft PIB sent to importer.
5. Customer confirmed or requested revision.
6. PIB submitted; AJU and billing recorded.
7. Customs billing paid.
8. Customs response received:
   - AP: permit/restriction analysis.
   - SPJK: document review.
   - SPJM: document submission and physical inspection.
   - SPPB: customs release approved.
9. SPPB issued.

Goods description, HS code, vessel/voyage, ports, and ETA should be editable during preparation rather than tied to unrelated payment milestones.

### 6.2 Track B — Shipping Line and Terminal

This work may proceed in parallel with customs processing:

1. THC processing/payment.
2. DO requested.
3. DO released.
4. Vessel arrived or container available at terminal.
5. Terminal/gate-pass administration ready.

### 6.3 Track C — Per-container Delivery

Each container should hold its own physical progress:

1. Delivery scheduled.
2. Gate out from terminal.
3. En route to consignee.
4. Arrived at consignee.
5. Unloading/unstuffing in progress.
6. Unloading/unstuffing finished.
7. Empty container returned.

Delivery should open only when:

- SPPB has been issued.
- DO has been released.
- Required terminal/THC administration is complete.

The shipment headline can summarize the least-advanced active container or show counts such as “1 of 3 delivered.”

## 7. Recommended customer timeline

Customers do not need every internal task. A future customer-facing timeline can group the workflow into eight stages:

1. **Documents received**
   We have received the shipment documents.

2. **Customs declaration being prepared**
   Documents are being checked and the Draft PIB is being prepared.

3. **Your confirmation is required**
   The Draft PIB is ready for customer review.

4. **Submitted to Customs**
   The declaration was submitted and customs processing has started.

5. **Customs processing**
   Show a plain-language detail when relevant: permit review, document review, or physical inspection.

6. **Customs cleared**
   SPPB has been issued.

7. **Delivery in progress**
   Show delivery scheduled, gate out, en route, and arrived separately for each container.

8. **Completed**
   Delivery is complete and every active empty container has been returned.

Vessel departure, ETA, and actual arrival should remain in a separate Sailing information block. The interface should distinguish:

- Latest actual update.
- Next estimated event.

## 8. Suggested customer wording

| Current wording | Suggested customer wording |
| :--- | :--- |
| Checking document | Documents under review |
| Checking Draft PIB to importir | Draft PIB under importer review |
| Waiting confirmation from customer | Your Draft PIB confirmation is required |
| Final sending PIB to custom | PIB submitted to Customs |
| Process payment THC | THC processing |
| Payment billing | Customs charges being processed |
| Response billing | Customs response received |
| Upload all document | Supporting customs documents submitted |
| Waiting process bahandle | Preparing for customs inspection |
| Payment bahandle | Inspection handling charges being processed |
| Container inspection | Customs physical inspection |
| Waiting change status SPJM to SPPB | Waiting for customs release approval |
| Container shipping schedule | Delivery scheduling |
| Container on the way factory | Container en route to consignee |
| Container arrived in factory | Container arrived at consignee |
| Loading in factory | Confirm whether this means unloading/unstuffing at consignee |

The technical AP/SPJK/SPJM/SPPB code may still be shown as secondary detail for customers who understand it.

## 9. Prioritized future options

No item below should be implemented without explicit client approval.

### Priority 0 — Business clarification only

- Decide whether Billing response is the initial or current customs response.
- Define what operators do for AP and SPJK.
- Confirm that only SPPB authorizes delivery scheduling.
- Define whether shipment delivery progress means all containers or any container.
- Confirm whether Loading in factory means unloading/unstuffing.
- Confirm how customers receive the Draft PIB.

### Priority 1 — Small customer-facing improvements

- Show a prominent current stage.
- Use customer-friendly aliases while retaining technical admin labels.
- Separate the latest actual event from the next ETA.
- Show the recorded container tracking URL as a link.
- Explain where to review the Draft PIB.
- Offer PIB confirmation only at the intended milestone.
- Ensure container Latest update uses customer-visible events only.

### Priority 2 — Workflow safeguards

- Add Save and Advance, or warn when advancing with unsaved changes.
- Warn when important stage information is missing.
- Prevent AP, SPJK, and SPJM from being treated as customs release.
- Record an explicit SPPB-issued event/date.
- Derive container status from gate-out and empty-return events where appropriate.
- Show Current milestone and Last updated on the admin shipment list.

### Priority 3 — Structural improvements

- Separate customs, shipping-line/terminal, and delivery tracks.
- Give each container independent delivery progress.
- Add actual arrival, SPPB, DO release, inspection, and richer per-container delivery appointment evidence.
- Support customer-visible shipment documents and Draft PIB versions.
- Add exception reasons and aging/SLA reporting.

## 10. Questions for the next client review

1. Does Billing response represent the initial channel or the latest response?
2. What exact operator action is expected after AP?
3. What exact operator action is expected after SPJK?
4. Must SPPB be recorded before delivery scheduling?
5. Should shipment progress represent all containers or the most advanced container?
6. Is Loading in factory actually unloading/unstuffing?
7. Is the Draft PIB sent through email/WhatsApp, or should it be downloadable?
8. Which internal payment and inspection steps should customers see?
9. Should customers see driver tracking positions?
10. Which dates or documents are required for audit and delay reporting?

## 11. External validation sources

The review used the following official Indonesian Customs references:

- Impor untuk Dipakai: https://www.beacukai.go.id/impor-untuk-dipakai
- Pemeriksaan Pabean FAQ: https://www.beacukai.go.id/faq-pemeriksaan-pabean
- BTKI and tariff/HS classification: https://www.beacukai.go.id/btki-dan-tarif

These sources support the following distinctions:

- PIB is prepared from supporting customs documents.
- HS classification and goods description are part of customs declaration preparation.
- SPJM requires red-channel physical-inspection handling.
- SPPB is the approval that permits goods to leave the customs area.

## 12. Decision boundary

This document does not alter or withdraw client approval of the current implementation. It records observations, operating safeguards, and optional future directions only.

Before implementing any recommendation:

1. Obtain the client’s explicit approval.
2. Update this document with the approved decision.
3. Create a small implementation plan under the repository’s one-step-at-a-time process.
4. Add or reset the relevant UAT checklist.

IMPORT

Step 1: Document received
B/L fields:
Customer name
Document received date (date)
Document received by (admin / operators name, for internal data)

Step 2: Checking document
B/L fields:
B/L number
Shipment mode (option: FCL | LCL | Air Shipment)

Step 3: Draft PIB
B/L fields:
Shipping line

Step 4: Checking draft PIB to importir
B/L fields:
Vessel name

Step 5: Waiting confirmation from customer
B/L fields:
Confirmation checklist (toggle)
Confirmed by (read-only note, shows who confirmed)

Step 6: Final sending PIB to custom (issuing billing)
B/L fields:
AJU number
Voyage number
Status billing (option: Not Issued | Issued)

Step 7: Process payment THC
B/L fields:
Port of loading

Step 8: Waiting release DO
B/L fields:
Departure date (date)

Step 9: DO release
B/L fields:
Port of discharge

Step 10: Payment billing
B/L fields:
Arrival time / ETA (date time)

Step 11: Response billing
B/L fields:
Billing response (option: SPPB | AP | SPJK | SPJM)
Description of goods
Packages
HS codes

Container fields:
Container Number
Container Size (option: 20 ft | 40 ft | 45 ft)
Seal number
Gross weight (kg)
CBM / measurement
Description of goods (default: same value as Description of goods from B/L, step 11)
Packages (default: same value as Packages from B/L, step 11)
HS codes (default: same value as HS codes from B/L, step 11)
Photo Door (image)
Photo Floor (image)
Photo Seal (image)
Photo EIR (image)
Additional Photos (image)

Additional step for SPJM
Step 12: Upload all document (SPJM branch only) -> change status only, no fields
Step 13: Waiting process bahandle (SPJM branch only) -> change status only, no fields
Step 14: Payment bahandle (SPJM branch only) -> change status only, no fields
Step 15: Container inspection (SPJM branch only) No fields -> change status only, no fields
Step 16: Waiting change status SPJM to SPPB (SPJM branch only) -> change status only, no fields

Step 17: Container shipping schedule
B/L fields:
Terminal name
Date of loading (date)
Loading destination

Step 18: Gate out from inbound terminal
Container fields:
Gate out CY (date time)
Driver name
No. License

Step 19: Container on the way factory
Container fields:
Tracking position driver
Tracking position (url)

Step 20: Container arrived in factory
Container fields:
Loading in factory status (option: On Process | Finished)

Step 21: Empty container returned
B/L fields:
Status (option: Draft | In Progress | Completed | Cancelled)
Completed at (date time)

Container fields:
Return depot name
Return date (date time)

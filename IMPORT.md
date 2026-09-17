IMPORT

OUTPUT - Process 1

Document Received -> progress
Customer -> field
Checking Document -> progress
Nomor AJU -> field
No. BL -> field

INPUT - Process 2

Draft PIB -> progress
Shipping Line -> field
Checking Draft PIB to Importir -> progress
Vessel Name -> field
Waiting Confirmation from Customer -> progress
Confirmation Checklist -> field (boolean)
Final Sending PIB to Custom (Bea Cukai), menerbitkan billing -> progress
Voyage -> field
Status Billing -> field
Process Payment THC -> progress
Port of Loading -> field
Waiting Release DO -> progress
Departure Date -> field
DO Release -> progress
Port of Discharging -> field
Payment Billing -> progress
Arrival Time / ETA -> field
Response Billing (SPPB/AP/SPJK/SPJM) -> progress
No. Container -> field (relationship, repeater)
No. Container -> subfield-container
No. AJU -> field
Response Billing -> field
Tambahan Step SPJM -> progress (conditional: Response Billing = SPJM)
Size Container -> subfield-container
Upload All Document -> progress
Description of Goods -> field
Waiting Process Bahandle -> progress
Hscode -> field
Payment Bahandle -> progress
Gross Weight -> subfield-container
Container Inspection -> progress
Packages -> field
Waiting Change Status of SPJM to SPPB -> progress
CBM / Measurement -> subfield-container
Container Shipping Schedule -> progress
Terminal Name -> field
Date of Loading -> field
Loading Destination -> field

FINAL - Process 3

Gate Out from Inbound Terminal for Delivery to Consignee -> progress
Gate Out CY -> subfield-container
Gate Out Date -> subfield-container
Gate Out Time -> subfield-container
Driver Name -> subfield-container
No. License -> subfield-container
Container On The Way Factory -> progress
Tracking Position Driver -> subfield-container
Tracking Position Input -> subfield-container (manual)
Container Arrived in Factory -> progress
Loading in Factory -> subfield-container
Loading in Factory Status -> subfield-container (ON-PROCESS | FINISHED)
Empty Container Returned -> progress
Return Empty Container Depot -> subfield-container
Return Depot Name -> subfield-container
Return Date -> subfield-container

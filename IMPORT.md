IMPORT

OUTPUT - Process 1

Document Received -> progress
Customer -> field
Checking Document -> progress
No. BL -> field

INPUT - Process 2

Draft PIB -> progress
Shipping Line -> field
Checking Draft PIB to Importir -> progress
Vessel Name -> field
Waiting Confirmation from Customer -> progress
Confirmation Checklist -> field (boolean)
Final Sending PIB to Custom (Bea Cukai), menerbitkan billing -> progress
Nomor AJU -> field
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
Size Container -> subfield-container
Description of Goods -> field (shipment level, seeded to each container, overridable)
Packages -> field (shipment level, seeded to each container, overridable)
Hscode -> field (shipment level, multiple per container, overridable)
Tambahan Step SPJM -> progress (conditional: Response Billing = SPJM)
Upload All Document -> progress
Waiting Process Bahandle -> progress
Payment Bahandle -> progress
Gross Weight -> subfield-container
Container Inspection -> progress
Waiting Change Status of SPJM to SPPB -> progress
CBM / Measurement -> subfield-container
Container Shipping Schedule -> progress

FINAL - Process 3

Gate Out from Inbound Terminal for Delivery to Consignee -> progress
Gate Out CY -> subfield-container
Gate Out Date -> subfield-container
Gate Out Time -> subfield-container
Driver Name -> subfield-container
No. License -> subfield-container
Container On The Way Factory -> progress
Tracking Position Driver -> subfield-container
Tracking Position (url) -> subfield-container (validated URL)
Container Arrived in Factory -> progress
Loading in Factory -> subfield-container
Loading in Factory Status -> subfield-container (ON-PROCESS | FINISHED)
Empty Container Returned -> progress
Return Empty Container Depot -> subfield-container
Return Depot Name -> subfield-container
Return Date -> subfield-container

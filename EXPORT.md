1. Process 1 — Booking & Document
   _Document Received_

Data:

- Shipment Type (export)
- Customer (relationship)
- Customer name snapshot (not editable except for admin & super_admin)
- Document Received Date (default today, editable)
- Document Received By (default current user, not editable except for admin & super_admin)

_Checking Booking Order_

Data:

- No. DO
- Shipping Line
- Vessel Name
- Voyage
- Port of Loading
- Port of Discharge
- Closing Time Depot
- Closing Time CY
- Shipment Mode

2. Process 2 — Container Preparation

_Pick Up Empty Container_

Data:

- Pick Up Depot
- Stuffing Date
- Stuffing Destination
- Container relationships

Each container data:

- Container Number
- Container Size
- Container Type
- Seal Number
- Driver name
- Vehicle / Truck Number
- Driver License Number
- Photo Door
- Photo Floor
- Photo Seal
- Photo EIR
- Additional Photos

3. Process 3 — Stuffing & Customs
   _Container On The Way Factory_

Container data:

- Tracking Position Driver
- Tracking Position Driver (url)

_Process Stuffing at Factory & Process PEB and NPE_

Container data:

- Progress Stuffing at Factory: ON-PROCESS | FINISHED

_Checking PEB and NPE_
Container data:

- Port of Loading: default will follow port of loading from bill of lading
- Gate In CY Date

_Process Gate In CY_
Container data:

- Amount of VGM (kg)

_Final Checking Details Shipment_
Container data:

- Final Checking Date
- Final Checking Checklist boolean

# Pharmacy API Routes

## Orders

### List Orders
`GET /pharmacy/orders`

**Query Params:**
- `pharmacy_id` (optional)
- `status` (optional)

---

### Create Order
`POST /pharmacy/orders`

**Payload:**
```json
{
  "pharmacy_id": 1,
  "patient_id": 2,
  "priority": "urgent", // or "normal"
  "status": "pending", // or "processing", "accepted", "completed", "delivered", "dispensed", "declined"
  "total_price": 100.50,
  "prescription_url": "https://...",
  "created_by": 3,
  "items": [
    { "medication_id": 1, "quantity": 2, "price": 50.25 }
  ]
}
```

---

### Get Order Details
`GET /pharmacy/orders/{id}`

---

### Update Order
`PUT /pharmacy/orders/{id}`

**Payload:**
```json
{
  "status": "completed",
  "updated_by": 3
}
```

---

### Delete Order
`DELETE /pharmacy/orders/{id}`

---

### Export Order
`GET /pharmacy/orders/{id}/export`

---

### EMR (Completed/Delivered Orders)
`GET /pharmacy/emr?pharmacy_id=1`

---

## Medications

### List Medications
`GET /pharmacy/medications`

**Query Params:**
- `pharmacy_id` (optional)
- `is_active` (optional)

---

### Create Medication
`POST /pharmacy/medications`

**Payload:**
```json
{
  "pharmacy_id": 1,
  "name": "Paracetamol",
  "description": "Pain relief",
  "stock": 100,
  "price": 5.00,
  "is_active": true,
  "created_by": 3
}
```

---

### Get Medication Details
`GET /pharmacy/medications/{id}`

---

### Update Medication
`PUT /pharmacy/medications/{id}`

**Payload:**
```json
{
  "name": "Ibuprofen",
  "stock": 80,
  "price": 6.00,
  "is_active": false,
  "updated_by": 3
}
```

---

### Delete Medication
`DELETE /pharmacy/medications/{id}`

---

## Statistics

### Get Pharmacy Statistics
`GET /pharmacy/statistics?pharmacy_id=1`

---

## Activities

### Get Recent Activities
`GET /pharmacy/activities?pharmacy_id=1&limit=20`

---

## Profile & Settings

### List Pharmacies
`GET /pharmacy/list`

---

### Get Pharmacy Details
`GET /pharmacy/{uuid}/details`

---

### Update Pharmacy Profile
`PUT /pharmacy/update/{id}`

**Payload:**
```json
{
  "pharmacy_name": "New Name",
  "pharmacy_license_number": "LIC1234",
  "pharmacist_in_charge_name": "Dr. John Doe",
  "pharmacy_phone": "+1234567890",
  "pharmacy_email": "pharmacy@email.com",
  "street_address": "123 Main St",
  "city": "Lagos",
  "state": "Lagos",
  "country": "Nigeria",
  "accept_terms": true,
  "status": "approved",
  "updated_by": 3
}
```

---

### Toggle Pharmacy Active Status
`PATCH /pharmacy/{id}/toggle-active`

(No payload required)

---

### Register Pharmacy
`POST /pharmacy/register`

**Payload:**
```json
{
  "pharmacy_name": "Pharmacy Name",
  "pharmacy_license_number": "LIC1234",
  "pharmacist_in_charge_name": "Dr. John Doe",
  "pharmacy_phone": "+1234567890",
  "pharmacy_email": "pharmacy@email.com",
  "street_address": "123 Main St",
  "city": "Lagos",
  "state": "Lagos",
  "country": "Nigeria",
  "accept_terms": true,
  "user_name": "John Doe",
  "user_email": "user@email.com",
  "user_phone": "+1234567890",
  "portal": "Pharmacy",
  "password": "password123"
}
``` 

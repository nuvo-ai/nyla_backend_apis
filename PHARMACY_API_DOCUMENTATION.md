# Pharmacy API Documentation

## Base URL
```
https://your-domain.com/api
```

## Authentication
All endpoints require authentication using Bearer token:
```
Authorization: Bearer {your-token}
```

---

## 1. Pharmacy Management

### 1.1 Register Pharmacy
**POST** `/pharmacy/register`

**Payload:**
```json
{
    "user_id": 1,
    "name": "City Pharmacy",
    "license_number": "PHARM123456",
    "pharmacist_in_charge_name": "Dr. John Smith",
    "phone": "+2348012345678",
    "email": "info@citypharmacy.com",
    "street_address": "123 Main Street",
    "city": "Lagos",
    "state": "Lagos",
    "country": "Nigeria",
    "google_maps_location": "https://maps.google.com/...",
    "delivery_available": true,
    "nafdac_certificate": "certificate_file_path",
    "request_onsite_setup": false,
    "terms_accepted": true
}
```

**Response:**
```json
{
    "success": true,
    "message": "Pharmacy registered successfully",
    "data": {
        "id": 1,
        "user_id": 1,
        "uuid": "pharm-uuid-123",
        "name": "City Pharmacy",
        "license_number": "PHARM123456",
        "pharmacist_in_charge_name": "Dr. John Smith",
        "phone": "+2348012345678",
        "email": "info@citypharmacy.com",
        "logo_path": null,
        "street_address": "123 Main Street",
        "city": "Lagos",
        "state": "Lagos",
        "country": "Nigeria",
        "google_maps_location": "https://maps.google.com/...",
        "delivery_available": true,
        "nafdac_certificate": "certificate_file_path",
        "request_onsite_setup": false,
        "terms_accepted": true,
        "status": "pending",
        "is_active": true,
        "created_at": "2025-08-06T10:00:00.000000Z",
        "updated_at": "2025-08-06T10:00:00.000000Z"
    },
    "code": 200
}
```

### 1.2 Update Pharmacy
**PUT** `/pharmacy/update/{id}`

**Payload:**
```json
{
    "name": "Updated City Pharmacy",
    "phone": "+2348012345679",
    "email": "updated@citypharmacy.com",
    "delivery_available": false
}
```

### 1.3 Get Pharmacy List
**GET** `/pharmacy/list`

**Query Parameters:**
- `status` (optional): pending, approved, rejected
- `is_active` (optional): true, false

**Response:**
```json
{
    "success": true,
    "message": "Pharmacies retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "City Pharmacy",
            "license_number": "PHARM123456",
            "email": "info@citypharmacy.com",
            "phone": "+2348012345678",
            "status": "approved",
            "is_active": true
        }
    ],
    "code": 200
}
```

### 1.4 Get Pharmacy Details
**GET** `/pharmacy/{uuid}/details`

**Response:**
```json
{
    "success": true,
    "message": "Pharmacy details retrieved successfully",
    "data": {
        "id": 1,
        "user_id": 1,
        "uuid": "pharm-uuid-123",
        "name": "City Pharmacy",
        "license_number": "PHARM123456",
        "pharmacist_in_charge_name": "Dr. John Smith",
        "phone": "+2348012345678",
        "email": "info@citypharmacy.com",
        "logo_url": "https://your-domain.com/storage/pharmacy_logos/logo.jpg",
        "street_address": "123 Main Street",
        "city": "Lagos",
        "state": "Lagos",
        "country": "Nigeria",
        "google_maps_location": "https://maps.google.com/...",
        "delivery_available": true,
        "nafdac_certificate_url": "https://your-domain.com/storage/certificates/cert.pdf",
        "request_onsite_setup": false,
        "terms_accepted": true,
        "status": "approved",
        "is_active": true,
        "created_at": "2025-08-06T10:00:00.000000Z",
        "updated_at": "2025-08-06T10:00:00.000000Z"
    },
    "code": 200
}
```

### 1.5 Toggle Pharmacy Active Status
**PATCH** `/pharmacy/{id}/toggle-active`

**Response:**
```json
{
    "success": true,
    "message": "Pharmacy status updated successfully",
    "data": {
        "id": 1,
        "is_active": false
    },
    "code": 200
}
```

---

## 2. Medication Types

### 2.1 Get Medication Types
**GET** `/pharmacy/medication-types`

**Query Parameters:**
- `is_active` (optional): true, false

**Response:**
```json
{
    "success": true,
    "message": "Medication types retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Antibiotics",
            "description": "Medications used to treat bacterial infections",
            "is_active": true,
            "created_at": "2025-08-06T10:00:00.000000Z",
            "updated_at": "2025-08-06T10:00:00.000000Z"
        },
        {
            "id": 2,
            "name": "Analgesics",
            "description": "Pain relief medications",
            "is_active": true,
            "created_at": "2025-08-06T10:00:00.000000Z",
            "updated_at": "2025-08-06T10:00:00.000000Z"
        }
    ],
    "code": 200
}
```

### 2.2 Create Medication Type
**POST** `/pharmacy/medication-types`

**Payload:**
```json
{
    "name": "Antihypertensives",
    "description": "Medications to treat high blood pressure",
    "is_active": true
}
```

**Response:**
```json
{
    "success": true,
    "message": "Medication type created successfully",
    "data": {
        "id": 3,
        "name": "Antihypertensives",
        "description": "Medications to treat high blood pressure",
        "is_active": true,
        "created_at": "2025-08-06T10:00:00.000000Z",
        "updated_at": "2025-08-06T10:00:00.000000Z"
    },
    "code": 200
}
```

### 2.3 Get Medication Type
**GET** `/pharmacy/medication-types/{id}`

### 2.4 Update Medication Type
**PUT** `/pharmacy/medication-types/{id}`

**Payload:**
```json
{
    "name": "Updated Antihypertensives",
    "description": "Updated description"
}
```

### 2.5 Delete Medication Type
**DELETE** `/pharmacy/medication-types/{id}`

---

## 3. Medications

### 3.1 Get Medications
**GET** `/pharmacy/medications`

**Query Parameters:**
- `pharmacy_id` (optional): Filter by pharmacy
- `medication_type_id` (optional): Filter by medication type
- `is_active` (optional): true, false

**Response:**
```json
{
    "success": true,
    "message": "Medications retrieved successfully",
    "data": [
        {
            "id": 1,
            "pharmacy_id": 1,
            "medication_type_id": 1,
            "name": "Paracetamol",
            "description": "Pain relief medication",
            "stock": 100,
            "price": "25.50",
            "is_active": true,
            "pharmacy": {
                "id": 1,
                "name": "City Pharmacy",
                "email": "info@citypharmacy.com"
            },
            "medication_type": {
                "id": 1,
                "name": "Analgesics",
                "description": "Pain relief medications"
            },
            "dosages": [
                {
                    "id": 1,
                    "medication_id": 1,
                    "strength": "500mg",
                    "form": "tablet",
                    "unit": "mg",
                    "quantity": "500.00",
                    "frequency": "every 4-6 hours",
                    "instructions": "Take with food if stomach upset occurs",
                    "is_active": true,
                    "full_dosage": "500.00mg tablet (every 4-6 hours)",
                    "created_at": "2025-08-06T10:00:00.000000Z",
                    "updated_at": "2025-08-06T10:00:00.000000Z"
                }
            ],
            "created_at": "2025-08-06T10:00:00.000000Z",
            "updated_at": "2025-08-06T10:00:00.000000Z"
        }
    ],
    "code": 200
}
```

### 3.2 Create Medication
**POST** `/pharmacy/medications`

**Payload:**
```json
{
    "pharmacy_id": 1,
    "medication_type_id": 1,
    "name": "Amoxicillin",
    "description": "Antibiotic medication",
    "stock": 50,
    "price": 15.75,
    "is_active": true
}
```

**Response:**
```json
{
    "success": true,
    "message": "Medication created successfully",
    "data": {
        "id": 2,
        "pharmacy_id": 1,
        "medication_type_id": 1,
        "name": "Amoxicillin",
        "description": "Antibiotic medication",
        "stock": 50,
        "price": "15.75",
        "is_active": true,
        "created_at": "2025-08-06T10:00:00.000000Z",
        "updated_at": "2025-08-06T10:00:00.000000Z"
    },
    "code": 200
}
```

### 3.3 Get Medication
**GET** `/pharmacy/medications/{id}`

### 3.4 Update Medication
**PUT** `/pharmacy/medications/{id}`

**Payload:**
```json
{
    "name": "Updated Amoxicillin",
    "description": "Updated description",
    "stock": 75,
    "price": 18.50
}
```

### 3.5 Delete Medication
**DELETE** `/pharmacy/medications/{id}`

---

## 4. Medication Dosages

### 4.1 Get Medication Dosages
**GET** `/pharmacy/medication-dosages`

**Query Parameters:**
- `medication_id` (optional): Filter by medication
- `is_active` (optional): true, false
- `form` (optional): tablet, capsule, liquid, injection

**Response:**
```json
{
    "success": true,
    "message": "Medication dosages retrieved successfully",
    "data": [
        {
            "id": 1,
            "medication_id": 1,
            "strength": "500mg",
            "form": "tablet",
            "unit": "mg",
            "quantity": "500.00",
            "frequency": "every 4-6 hours",
            "instructions": "Take with food if stomach upset occurs",
            "is_active": true,
            "full_dosage": "500.00mg tablet (every 4-6 hours)",
            "medication": {
                "id": 1,
                "name": "Paracetamol",
                "description": "Pain relief medication"
            },
            "created_at": "2025-08-06T10:00:00.000000Z",
            "updated_at": "2025-08-06T10:00:00.000000Z"
        }
    ],
    "code": 200
}
```

### 4.2 Create Medication Dosage
**POST** `/pharmacy/medication-dosages`

**Payload:**
```json
{
    "medication_id": 1,
    "strength": "1000mg",
    "form": "tablet",
    "unit": "mg",
    "quantity": 1000,
    "frequency": "twice daily",
    "instructions": "Take with a full glass of water",
    "is_active": true
}
```

**Response:**
```json
{
    "success": true,
    "message": "Medication dosage created successfully",
    "data": {
        "id": 2,
        "medication_id": 1,
        "strength": "1000mg",
        "form": "tablet",
        "unit": "mg",
        "quantity": "1000.00",
        "frequency": "twice daily",
        "instructions": "Take with a full glass of water",
        "is_active": true,
        "full_dosage": "1000.00mg tablet (twice daily)",
        "medication": {
            "id": 1,
            "name": "Paracetamol",
            "description": "Pain relief medication"
        },
        "created_at": "2025-08-06T10:00:00.000000Z",
        "updated_at": "2025-08-06T10:00:00.000000Z"
    },
    "code": 200
}
```

### 4.3 Get Medication Dosage
**GET** `/pharmacy/medication-dosages/{id}`

### 4.4 Update Medication Dosage
**PUT** `/pharmacy/medication-dosages/{id}`

**Payload:**
```json
{
    "strength": "750mg",
    "quantity": 750,
    "frequency": "three times daily"
}
```

### 4.5 Delete Medication Dosage
**DELETE** `/pharmacy/medication-dosages/{id}`

### 4.6 Get Dosages by Medication
**GET** `/pharmacy/medications/{medicationId}/dosages`

**Response:**
```json
{
    "success": true,
    "message": "Medication dosages retrieved successfully",
    "data": [
        {
            "id": 1,
            "medication_id": 1,
            "strength": "500mg",
            "form": "tablet",
            "unit": "mg",
            "quantity": "500.00",
            "frequency": "every 4-6 hours",
            "instructions": "Take with food if stomach upset occurs",
            "is_active": true,
            "full_dosage": "500.00mg tablet (every 4-6 hours)",
            "medication": {
                "id": 1,
                "name": "Paracetamol",
                "description": "Pain relief medication"
            },
            "created_at": "2025-08-06T10:00:00.000000Z",
            "updated_at": "2025-08-06T10:00:00.000000Z"
        },
        {
            "id": 2,
            "medication_id": 1,
            "strength": "1000mg",
            "form": "tablet",
            "unit": "mg",
            "quantity": "1000.00",
            "frequency": "twice daily",
            "instructions": "Take with a full glass of water",
            "is_active": true,
            "full_dosage": "1000.00mg tablet (twice daily)",
            "medication": {
                "id": 1,
                "name": "Paracetamol",
                "description": "Pain relief medication"
            },
            "created_at": "2025-08-06T10:00:00.000000Z",
            "updated_at": "2025-08-06T10:00:00.000000Z"
        }
    ],
    "code": 200
}
```

### 4.7 Get Available Forms for Medication
**GET** `/pharmacy/medications/{medicationId}/forms`

**Response:**
```json
{
    "success": true,
    "message": "Available forms retrieved successfully",
    "data": ["tablet", "liquid", "capsule"],
    "code": 200
}
```

---

## 5. Orders

### 5.1 Get Orders
**GET** `/pharmacy/orders`

**Query Parameters:**
- `pharmacy_id` (optional): Filter by pharmacy
- `status` (optional): pending, processing, accepted, completed, delivered, dispensed, declined

**Response:**
```json
{
    "success": true,
    "message": "Orders retrieved successfully",
    "data": [
        {
            "id": 1,
            "pharmacy_id": 1,
            "patient_id": 2,
            "priority": "normal",
            "status": "pending",
            "total_price": "25.50",
            "prescription_url": null,
            "order_note": "Please deliver in the morning",
            "created_by": 2,
            "pharmacy": {
                "id": 1,
                "name": "City Pharmacy",
                "email": "info@citypharmacy.com",
                "phone": "+2348012345678"
            },
            "patient": {
                "id": 2,
                "name": "John Doe",
                "email": "john@example.com",
                "phone": "+2348012345679"
            },
            "creator": {
                "id": 2,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "items": [
                {
                    "id": 1,
                    "order_id": 1,
                    "medication_id": 1,
                    "quantity": 1,
                    "price": "25.50",
                    "status": "pending",
                    "medication": {
                        "id": 1,
                        "name": "Paracetamol",
                        "description": "Pain relief medication",
                        "price": "25.50",
                        "stock": 100
                    },
                    "created_at": "2025-08-06T10:00:00.000000Z",
                    "updated_at": "2025-08-06T10:00:00.000000Z"
                }
            ],
            "medications": [
                {
                    "id": 1,
                    "name": "Paracetamol",
                    "description": "Pain relief medication",
                    "price": "25.50"
                }
            ],
            "created_at": "2025-08-06T10:00:00.000000Z",
            "updated_at": "2025-08-06T10:00:00.000000Z"
        }
    ],
    "code": 200
}
```

### 5.2 Create Order
**POST** `/pharmacy/orders`

**Payload:**
```json
{
    "pharmacy_id": 1,
    "patient_id": 2,
    "priority": "normal",
    "status": "pending",
    "total_price": 25.50,
    "prescription_url": "https://example.com/prescription.pdf",
    "order_note": "Please deliver in the morning",
    "created_by": 2,
    "items": [
        {
            "medication_id": 1,
            "quantity": 1,
            "price": 25.50,
            "status": "pending"
        },
        {
            "medication_id": 2,
            "quantity": 2,
            "price": 15.75,
            "status": "pending"
        }
    ]
}
```

**Response:**
```json
{
    "success": true,
    "message": "Order created successfully",
    "data": {
        "id": 1,
        "pharmacy_id": 1,
        "patient_id": 2,
        "priority": "normal",
        "status": "pending",
        "total_price": "57.00",
        "prescription_url": "https://example.com/prescription.pdf",
        "order_note": "Please deliver in the morning",
        "created_by": 2,
        "pharmacy": {
            "id": 1,
            "name": "City Pharmacy",
            "email": "info@citypharmacy.com",
            "phone": "+2348012345678"
        },
        "patient": {
            "id": 2,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "+2348012345679"
        },
        "creator": {
            "id": 2,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "items": [
            {
                "id": 1,
                "order_id": 1,
                "medication_id": 1,
                "quantity": 1,
                "price": "25.50",
                "status": "pending",
                "medication": {
                    "id": 1,
                    "name": "Paracetamol",
                    "description": "Pain relief medication",
                    "price": "25.50",
                    "stock": 100
                },
                "created_at": "2025-08-06T10:00:00.000000Z",
                "updated_at": "2025-08-06T10:00:00.000000Z"
            },
            {
                "id": 2,
                "order_id": 1,
                "medication_id": 2,
                "quantity": 2,
                "price": "15.75",
                "status": "pending",
                "medication": {
                    "id": 2,
                    "name": "Amoxicillin",
                    "description": "Antibiotic medication",
                    "price": "15.75",
                    "stock": 50
                },
                "created_at": "2025-08-06T10:00:00.000000Z",
                "updated_at": "2025-08-06T10:00:00.000000Z"
            }
        ],
        "medications": [
            {
                "id": 1,
                "name": "Paracetamol",
                "description": "Pain relief medication",
                "price": "25.50"
            },
            {
                "id": 2,
                "name": "Amoxicillin",
                "description": "Antibiotic medication",
                "price": "15.75"
            }
        ],
        "created_at": "2025-08-06T10:00:00.000000Z",
        "updated_at": "2025-08-06T10:00:00.000000Z"
    },
    "code": 200
}
```

### 5.3 Get Order
**GET** `/pharmacy/orders/{id}`

### 5.4 Update Order
**PUT** `/pharmacy/orders/{id}`

**Payload:**
```json
{
    "status": "processing",
    "order_note": "Updated delivery instructions"
}
```

### 5.5 Delete Order
**DELETE** `/pharmacy/orders/{id}`

### 5.6 Export Order
**GET** `/pharmacy/orders/{id}/export`

### 5.7 Get EMR (Electronic Medical Records)
**GET** `/pharmacy/emr`

**Query Parameters:**
- `pharmacy_id` (required): Pharmacy ID

**Response:**
```json
{
    "success": true,
    "message": "EMR retrieved successfully",
    "data": [
        {
            "id": 1,
            "pharmacy_id": 1,
            "patient_id": 2,
            "priority": "normal",
            "status": "completed",
            "total_price": "25.50",
            "prescription_url": null,
            "order_note": "Delivered successfully",
            "created_by": 2,
            "patient": {
                "id": 2,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "items": [
                {
                    "id": 1,
                    "order_id": 1,
                    "medication_id": 1,
                    "quantity": 1,
                    "price": "25.50",
                    "status": "completed",
                    "medication": {
                        "id": 1,
                        "name": "Paracetamol",
                        "description": "Pain relief medication",
                        "price": "25.50",
                        "stock": 100
                    }
                }
            ],
            "created_at": "2025-08-06T10:00:00.000000Z",
            "updated_at": "2025-08-06T10:00:00.000000Z"
        }
    ],
    "code": 200
}
```

### 5.8 Get Patient Order History
**GET** `/pharmacy/patient/orders`

**Query Parameters:**
- `status` (optional): pending, processing, accepted, completed, delivered, dispensed, declined

**Response:**
```json
{
    "success": true,
    "message": "Patient order history retrieved successfully",
    "data": [
        {
            "id": 1,
            "pharmacy_id": 1,
            "patient_id": 2,
            "priority": "normal",
            "status": "completed",
            "total_price": "25.50",
            "prescription_url": null,
            "order_note": "Please deliver in the morning",
            "created_by": 2,
            "pharmacy": {
                "id": 1,
                "name": "City Pharmacy",
                "email": "info@citypharmacy.com",
                "phone": "+2348012345678"
            },
            "patient": {
                "id": 2,
                "name": "John Doe",
                "email": "john@example.com",
                "phone": "+2348012345679"
            },
            "creator": {
                "id": 2,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "items": [
                {
                    "id": 1,
                    "order_id": 1,
                    "medication_id": 1,
                    "quantity": 1,
                    "price": "25.50",
                    "status": "completed",
                    "medication": {
                        "id": 1,
                        "name": "Paracetamol",
                        "description": "Pain relief medication",
                        "price": "25.50",
                        "stock": 100
                    },
                    "created_at": "2025-08-06T10:00:00.000000Z",
                    "updated_at": "2025-08-06T10:00:00.000000Z"
                }
            ],
            "medications": [
                {
                    "id": 1,
                    "name": "Paracetamol",
                    "description": "Pain relief medication",
                    "price": "25.50"
                }
            ],
            "created_at": "2025-08-06T10:00:00.000000Z",
            "updated_at": "2025-08-06T10:00:00.000000Z"
        },
        {
            "id": 2,
            "pharmacy_id": 1,
            "patient_id": 2,
            "priority": "urgent",
            "status": "pending",
            "total_price": "50.00",
            "prescription_url": null,
            "order_note": "Urgent delivery needed",
            "created_by": 2,
            "pharmacy": {
                "id": 1,
                "name": "City Pharmacy",
                "email": "info@citypharmacy.com",
                "phone": "+2348012345678"
            },
            "patient": {
                "id": 2,
                "name": "John Doe",
                "email": "john@example.com",
                "phone": "+2348012345679"
            },
            "creator": {
                "id": 2,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "items": [
                {
                    "id": 2,
                    "order_id": 2,
                    "medication_id": 2,
                    "quantity": 2,
                    "price": "25.00",
                    "status": "pending",
                    "medication": {
                        "id": 2,
                        "name": "Amoxicillin",
                        "description": "Antibiotic medication",
                        "price": "25.00",
                        "stock": 50
                    },
                    "created_at": "2025-08-06T10:00:00.000000Z",
                    "updated_at": "2025-08-06T10:00:00.000000Z"
                }
            ],
            "medications": [
                {
                    "id": 2,
                    "name": "Amoxicillin",
                    "description": "Antibiotic medication",
                    "price": "25.00"
                }
            ],
            "created_at": "2025-08-06T10:00:00.000000Z",
            "updated_at": "2025-08-06T10:00:00.000000Z"
        }
    ],
    "code": 200
}
```

### 5.9 Get Pharmacy Statistics
**GET** `/statistics`

**Query Parameters:**
- `pharmacy_id` (required): Pharmacy ID

**Response:**
```json
{
    "success": true,
    "message": "Pharmacy statistics retrieved successfully",
    "data": {
        "total_orders": 150,
        "total_revenue": "3750.00",
        "total_patients": 45,
        "total_orders_dispensed": 120,
        "total_orders_completed": 130,
        "total_orders_delivered": 125
    },
    "code": 200
}
```

---

## 6. Pharmacy Activities

### 6.1 Get Pharmacy Activities
**GET** `/activities`

**Query Parameters:**
- `pharmacy_id` (optional): Filter by pharmacy
- `user_id` (optional): Filter by user
- `action` (optional): Filter by action type

**Response:**
```json
{
    "success": true,
    "message": "Activities retrieved successfully",
    "data": [
        {
            "id": 1,
            "pharmacy_id": 1,
            "user_id": 2,
            "action": "Order created",
            "details": {
                "order_id": 1,
                "total_price": "25.50"
            },
            "created_at": "2025-08-06T10:00:00.000000Z",
            "updated_at": "2025-08-06T10:00:00.000000Z"
        },
        {
            "id": 2,
            "pharmacy_id": 1,
            "user_id": 2,
            "action": "Medication created",
            "details": {
                "medication_id": 1,
                "name": "Paracetamol"
            },
            "created_at": "2025-08-06T09:30:00.000000Z",
            "updated_at": "2025-08-06T09:30:00.000000Z"
        }
    ],
    "code": 200
}
```

---

## Error Responses

### Validation Error (422)
```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "pharmacy_id": ["The pharmacy id field is required."],
        "name": ["The name field is required."]
    },
    "code": 422
}
```

### Not Found Error (404)
```json
{
    "success": false,
    "message": "Pharmacy not found",
    "errors": null,
    "code": 404
}
```

### Server Error (500)
```json
{
    "success": false,
    "message": "Internal server error",
    "errors": null,
    "code": 500
}
```

---

## Status Codes

- **200**: Success
- **201**: Created
- **400**: Bad Request
- **401**: Unauthorized
- **404**: Not Found
- **422**: Validation Error
- **500**: Internal Server Error

---

## Notes for Postman

1. **Base URL**: Set as environment variable
2. **Authentication**: Add Bearer token to all requests
3. **Content-Type**: Set to `application/json` for POST/PUT requests
4. **File Uploads**: Use `multipart/form-data` for file uploads (prescriptions, certificates)
5. **Query Parameters**: Use for filtering and pagination
6. **Response Format**: All responses follow the same structure with `success`, `message`, `data`, and `code` fields

This documentation covers all pharmacy-related endpoints with their payloads and example responses to guide your Postman documentation. 

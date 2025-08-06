# Nyla AI Command Center - API Endpoints Specification

## Overview
This document outlines all the required API endpoints for the Nyla AI Command Center - a healthcare platform admin dashboard. The current frontend is built with React/TypeScript and requires a backend API to replace the current mock JSONPlaceholder implementation.

## Base Configuration
- **Base URL**: `https://api.nyla-ai.com` (or your preferred domain)
- **Authentication**: JWT Bearer tokens
- **Content-Type**: `application/json`
- **API Version**: `v1`

## Current Frontend Structure
The admin dashboard includes the following main sections:
- Dashboard (analytics overview)
- User Management (patient/user administration)
- Hospital Management (healthcare provider management)
- Pharmacy Management (pharmacy partner administration)
- Analytics (detailed reporting)
- Notifications Center (communication management)
- Settings Panel (system configuration)

---

## 1. Authentication & Authorization

### Login
```
POST /api/v1/auth/login
Body: {
  "email": "string",
  "password": "string"
}
Response: {
  "token": "string",
  "refreshToken": "string",
  "user": {
    "id": "number",
    "email": "string",
    "role": "admin|super_admin|moderator"
  }
}
```

### Logout
```
POST /api/v1/auth/logout
Headers: Authorization: Bearer {token}
Response: {
  "message": "Logged out successfully"
}
```

### Refresh Token
```
POST /api/v1/auth/refresh
Body: {
  "refreshToken": "string"
}
Response: {
  "token": "string",
  "refreshToken": "string"
}
```

### Get Current User
```
GET /api/v1/auth/me
Headers: Authorization: Bearer {token}
Response: {
  "id": "number",
  "email": "string",
  "role": "string",
  "permissions": ["string"]
}
```

### Password Management
```
POST /api/v1/auth/forgot-password
Body: {
  "email": "string"
}

POST /api/v1/auth/reset-password
Body: {
  "token": "string",
  "newPassword": "string"
}

POST /api/v1/auth/change-password
Headers: Authorization: Bearer {token}
Body: {
  "currentPassword": "string",
  "newPassword": "string"
}
```

---

## 2. User Management

### List Users
```
GET /api/v1/users
Query Parameters:
  - page: number (default: 1)
  - limit: number (default: 20)
  - status: "active|inactive|suspended"
  - role: "patient|doctor|admin"
  - country: "nigeria|ghana|kenya|south-africa"
  - region: "west-africa|east-africa|southern-africa"
  - gender: "male|female|other"
  - search: string

Response: {
  "users": [{
    "id": "number",
    "name": "string",
    "email": "string",
    "status": "active|inactive|suspended",
    "role": "patient|doctor|admin",
    "joinDate": "string",
    "lastActive": "string",
    "country": "string",
    "region": "string",
    "gender": "string"
  }],
  "pagination": {
    "page": "number",
    "limit": "number",
    "total": "number",
    "totalPages": "number"
  }
}
```

### Get User Details
```
GET /api/v1/users/{id}
Response: {
  "id": "number",
  "name": "string",
  "email": "string",
  "phone": "string",
  "status": "string",
  "role": "string",
  "joinDate": "string",
  "lastActive": "string",
  "country": "string",
  "region": "string",
  "gender": "string",
  "profilePicture": "string",
  "conversationCount": "number",
  "appointmentCount": "number"
}
```

### Create User
```
POST /api/v1/users
Body: {
  "name": "string",
  "email": "string",
  "phone": "string",
  "role": "patient|doctor|admin",
  "country": "string",
  "region": "string",
  "gender": "string"
}
```

### Update User
```
PUT /api/v1/users/{id}
Body: {
  "name": "string",
  "email": "string",
  "phone": "string",
  "status": "string",
  "role": "string"
}
```

### User Actions
```
PATCH /api/v1/users/{id}/suspend
PATCH /api/v1/users/{id}/reactivate
DELETE /api/v1/users/{id}
```

### User Statistics
```
GET /api/v1/users/stats
Response: {
  "totalUsers": "number",
  "activeUsers": "number",
  "newUsersThisMonth": "number",
  "genderBreakdown": {
    "male": "number",
    "female": "number",
    "other": "number"
  },
  "countryDistribution": {
    "nigeria": "number",
    "ghana": "number",
    "kenya": "number",
    "south-africa": "number"
  }
}
```

### Export Users
```
GET /api/v1/users/export
Query Parameters:
  - format: "csv|xlsx"
  - filters: same as list users
Response: File download
```

---

## 3. Hospital Management

### List Hospitals
```
GET /api/v1/hospitals
Query Parameters:
  - page: number
  - limit: number
  - status: "active|pending|suspended"
  - country: string
  - region: string
  - search: string

Response: {
  "hospitals": [{
    "id": "number",
    "name": "string",
    "location": "string",
    "email": "string",
    "phone": "string",
    "status": "active|pending|suspended",
    "joinDate": "string",
    "patients": "number",
    "appointments": "number",
    "specialties": ["string"],
    "country": "string",
    "region": "string"
  }],
  "pagination": {...}
}
```

### Get Hospital Details
```
GET /api/v1/hospitals/{id}
Response: {
  "id": "number",
  "name": "string",
  "location": "string",
  "address": "string",
  "email": "string",
  "phone": "string",
  "website": "string",
  "status": "string",
  "joinDate": "string",
  "patients": "number",
  "appointments": "number",
  "specialties": ["string"],
  "country": "string",
  "region": "string",
  "licenseNumber": "string",
  "accreditation": "string",
  "contactPerson": {
    "name": "string",
    "email": "string",
    "phone": "string"
  }
}
```

### Hospital Registration & Management
```
POST /api/v1/hospitals
PUT /api/v1/hospitals/{id}
DELETE /api/v1/hospitals/{id}
```

### Hospital Actions
```
PATCH /api/v1/hospitals/{id}/approve
PATCH /api/v1/hospitals/{id}/reject
PATCH /api/v1/hospitals/{id}/suspend
```

### Pending Hospitals
```
GET /api/v1/hospitals/pending
Response: {
  "hospitals": [...],
  "count": "number"
}
```

### Hospital Statistics
```
GET /api/v1/hospitals/stats
Response: {
  "totalHospitals": "number",
  "activeHospitals": "number",
  "pendingApprovals": "number",
  "totalPatients": "number",
  "totalAppointments": "number",
  "topSpecialties": [{
    "name": "string",
    "count": "number"
  }]
}
```

---

## 4. Pharmacy Management

### List Pharmacies
```
GET /api/v1/pharmacies
Query Parameters: (similar to hospitals)

Response: {
  "pharmacies": [{
    "id": "number",
    "name": "string",
    "location": "string",
    "email": "string",
    "phone": "string",
    "status": "active|pending|suspended",
    "joinDate": "string",
    "orders": "number",
    "revenue": "number",
    "rating": "string",
    "services": ["string"],
    "country": "string",
    "region": "string"
  }],
  "pagination": {...}
}
```

### Get Pharmacy Details
```
GET /api/v1/pharmacies/{id}
Response: {
  "id": "number",
  "name": "string",
  "location": "string",
  "address": "string",
  "email": "string",
  "phone": "string",
  "website": "string",
  "status": "string",
  "joinDate": "string",
  "orders": "number",
  "revenue": "number",
  "rating": "string",
  "services": ["string"],
  "country": "string",
  "region": "string",
  "licenseNumber": "string",
  "operatingHours": "string",
  "deliveryRadius": "number",
  "contactPerson": {
    "name": "string",
    "email": "string",
    "phone": "string"
  }
}
```

### Pharmacy Management
```
POST /api/v1/pharmacies
PUT /api/v1/pharmacies/{id}
DELETE /api/v1/pharmacies/{id}
```

### Pharmacy Actions
```
PATCH /api/v1/pharmacies/{id}/approve
PATCH /api/v1/pharmacies/{id}/reject
PATCH /api/v1/pharmacies/{id}/suspend
```

### Pharmacy Statistics
```
GET /api/v1/pharmacies/stats
Response: {
  "totalPharmacies": "number",
  "activePharmacies": "number",
  "pendingApprovals": "number",
  "totalOrders": "number",
  "totalRevenue": "number",
  "averageRating": "number",
  "topServices": [{
    "name": "string",
    "count": "number"
  }]
}
```

---

## 5. Analytics & Reporting

### Dashboard Metrics
```
GET /api/v1/analytics/dashboard
Response: {
  "totalUsers": "number",
  "activeUsers": "number",
  "totalConversations": "number",
  "totalAppointments": "number",
  "totalOrders": "number",
  "averageRating": "number",
  "monthlyGrowth": "number",
  "userGrowthData": [{
    "month": "string",
    "users": "number",
    "conversations": "number",
    "orders": "number",
    "appointments": "number"
  }],
  "usageDistribution": [{
    "name": "string",
    "value": "number",
    "color": "string"
  }],
  "genderBreakdown": {
    "total": {
      "male": "number",
      "female": "number",
      "other": "number"
    },
    "active": {
      "male": "number",
      "female": "number",
      "other": "number"
    }
  }
}
```

### User Analytics
```
GET /api/v1/analytics/users
Query Parameters:
  - startDate: string
  - endDate: string
  - groupBy: "day|week|month"

Response: {
  "totalUsers": "number",
  "newUsers": "number",
  "activeUsers": "number",
  "retentionRate": "number",
  "chartData": [{
    "period": "string",
    "newUsers": "number",
    "activeUsers": "number"
  }],
  "demographics": {...}
}
```

### Conversation Analytics
```
GET /api/v1/analytics/conversations
Response: {
  "totalConversations": "number",
  "averageLength": "number",
  "satisfactionScore": "number",
  "categoryBreakdown": [{
    "category": "string",
    "count": "number",
    "percentage": "number"
  }],
  "dailyActivity": [{
    "day": "string",
    "conversations": "number",
    "users": "number"
  }]
}
```

### Appointment Analytics
```
GET /api/v1/analytics/appointments
Response: {
  "totalAppointments": "number",
  "completedAppointments": "number",
  "cancelledAppointments": "number",
  "completionRate": "number",
  "averageWaitTime": "number",
  "specialtyBreakdown": [{
    "specialty": "string",
    "count": "number"
  }]
}
```

### Order Analytics
```
GET /api/v1/analytics/orders
Response: {
  "totalOrders": "number",
  "completedOrders": "number",
  "totalRevenue": "number",
  "averageOrderValue": "number",
  "topMedications": [{
    "name": "string",
    "count": "number"
  }],
  "revenueByMonth": [{
    "month": "string",
    "revenue": "number"
  }]
}
```

### Geographic Analytics
```
GET /api/v1/analytics/geographic
Response: {
  "countryDistribution": [{
    "country": "string",
    "users": "number",
    "hospitals": "number",
    "pharmacies": "number"
  }],
  "regionDistribution": [{
    "region": "string",
    "users": "number",
    "growth": "number"
  }]
}
```

### Export Analytics
```
GET /api/v1/analytics/export
Query Parameters:
  - type: "users|conversations|appointments|orders"
  - format: "csv|xlsx|pdf"
  - startDate: string
  - endDate: string
Response: File download
```

---

## 6. Notifications & Communications

### List Notifications
```
GET /api/v1/notifications
Query Parameters:
  - page: number
  - limit: number
  - status: "sent|scheduled|draft"
  - type: "system|promotional|alert"

Response: {
  "notifications": [{
    "id": "number",
    "title": "string",
    "message": "string",
    "type": "system|promotional|alert",
    "status": "sent|scheduled|draft",
    "recipients": "number",
    "scheduledAt": "string",
    "sentAt": "string",
    "createdAt": "string"
  }],
  "pagination": {...}
}
```

### Create Notification
```
POST /api/v1/notifications
Body: {
  "title": "string",
  "message": "string",
  "type": "system|promotional|alert",
  "recipients": {
    "type": "all|specific|filtered",
    "userIds": ["number"],
    "filters": {
      "country": "string",
      "region": "string",
      "userType": "string"
    }
  },
  "scheduledAt": "string" // optional
}
```

### Update/Delete Notifications
```
PUT /api/v1/notifications/{id}
DELETE /api/v1/notifications/{id}
```

### Notification Actions
```
PATCH /api/v1/notifications/{id}/send
PATCH /api/v1/notifications/{id}/cancel
```

### Broadcast Notification
```
POST /api/v1/notifications/broadcast
Body: {
  "title": "string",
  "message": "string",
  "type": "urgent|info|warning",
  "recipients": "all|admins|users"
}
```

### Notification Templates
```
GET /api/v1/notifications/templates
POST /api/v1/notifications/templates
PUT /api/v1/notifications/templates/{id}
DELETE /api/v1/notifications/templates/{id}
```

---

## 7. Content Management

### Health Articles
```
GET /api/v1/content/articles
POST /api/v1/content/articles
PUT /api/v1/content/articles/{id}
DELETE /api/v1/content/articles/{id}

Article Structure: {
  "id": "number",
  "title": "string",
  "content": "string",
  "category": "string",
  "tags": ["string"],
  "author": "string",
  "status": "published|draft|archived",
  "publishedAt": "string",
  "updatedAt": "string"
}
```

### FAQ Management
```
GET /api/v1/content/faqs
POST /api/v1/content/faqs
PUT /api/v1/content/faqs/{id}
DELETE /api/v1/content/faqs/{id}

FAQ Structure: {
  "id": "number",
  "question": "string",
  "answer": "string",
  "category": "string",
  "order": "number",
  "isActive": "boolean"
}
```

---

## 8. System Settings & Configuration

### General Settings
```
GET /api/v1/settings/general
PUT /api/v1/settings/general

Settings Structure: {
  "siteName": "string",
  "siteDescription": "string",
  "contactEmail": "string",
  "supportPhone": "string",
  "maintenanceMode": "boolean",
  "registrationEnabled": "boolean"
}
```

### Notification Settings
```
GET /api/v1/settings/notifications
PUT /api/v1/settings/notifications

Settings Structure: {
  "emailNotifications": "boolean",
  "smsNotifications": "boolean",
  "pushNotifications": "boolean",
  "notificationFrequency": "immediate|daily|weekly",
  "emailTemplates": {...}
}
```

### Security Settings
```
GET /api/v1/settings/security
PUT /api/v1/settings/security

Settings Structure: {
  "passwordPolicy": {
    "minLength": "number",
    "requireSpecialChars": "boolean",
    "requireNumbers": "boolean",
    "requireUppercase": "boolean"
  },
  "sessionTimeout": "number",
  "twoFactorAuth": "boolean",
  "ipWhitelist": ["string"]
}
```

### Integration Settings
```
GET /api/v1/settings/integrations
PUT /api/v1/settings/integrations

Settings Structure: {
  "paymentGateway": {
    "provider": "string",
    "apiKey": "string",
    "webhookUrl": "string"
  },
  "smsProvider": {
    "provider": "string",
    "apiKey": "string"
  },
  "emailProvider": {
    "provider": "string",
    "apiKey": "string"
  }
}
```

---

## 9. Audit & Logging

### System Audit Logs
```
GET /api/v1/audit/logs
Query Parameters:
  - startDate: string
  - endDate: string
  - level: "info|warning|error"
  - module: string
  - page: number
  - limit: number

Response: {
  "logs": [{
    "id": "number",
    "timestamp": "string",
    "level": "info|warning|error",
    "module": "string",
    "action": "string",
    "details": "string",
    "userId": "number",
    "ipAddress": "string"
  }],
  "pagination": {...}
}
```

### User Activity Logs
```
GET /api/v1/audit/user-activity
Query Parameters:
  - userId: number
  - startDate: string
  - endDate: string
  - action: string

Response: {
  "activities": [{
    "id": "number",
    "userId": "number",
    "action": "string",
    "details": "string",
    "timestamp": "string",
    "ipAddress": "string",
    "userAgent": "string"
  }]
}
```

### Admin Action Logs
```
GET /api/v1/audit/admin-actions
Response: {
  "actions": [{
    "id": "number",
    "adminId": "number",
    "adminName": "string",
    "action": "string",
    "targetType": "user|hospital|pharmacy",
    "targetId": "number",
    "details": "string",
    "timestamp": "string"
  }]
}
```

### Security Events
```
GET /api/v1/audit/security-events
Response: {
  "events": [{
    "id": "number",
    "type": "failed_login|suspicious_activity|data_breach",
    "severity": "low|medium|high|critical",
    "description": "string",
    "userId": "number",
    "ipAddress": "string",
    "timestamp": "string",
    "resolved": "boolean"
  }]
}
```

---

## 10. Support & Help Desk

### Support Tickets
```
GET /api/v1/support/tickets
Query Parameters:
  - status: "open|in_progress|resolved|closed"
  - priority: "low|medium|high|urgent"
  - assignedTo: number
  - page: number
  - limit: number

Response: {
  "tickets": [{
    "id": "number",
    "subject": "string",
    "description": "string",
    "status": "open|in_progress|resolved|closed",
    "priority": "low|medium|high|urgent",
    "userId": "number",
    "userName": "string",
    "assignedTo": "number",
    "assignedToName": "string",
    "createdAt": "string",
    "updatedAt": "string"
  }],
  "pagination": {...}
}
```

### Create Support Ticket
```
POST /api/v1/support/tickets
Body: {
  "subject": "string",
  "description": "string",
  "priority": "low|medium|high|urgent",
  "userId": "number",
  "category": "technical|billing|general"
}
```

### Update Support Ticket
```
PUT /api/v1/support/tickets/{id}
Body: {
  "status": "string",
  "assignedTo": "number",
  "priority": "string",
  "notes": "string"
}
```

### Get Ticket Details
```
GET /api/v1/support/tickets/{id}
Response: {
  "ticket": {...},
  "messages": [{
    "id": "number",
    "message": "string",
    "authorId": "number",
    "authorName": "string",
    "authorType": "user|admin",
    "timestamp": "string",
    "attachments": ["string"]
  }]
}
```

### Close Ticket
```
PATCH /api/v1/support/tickets/{id}/close
Body: {
  "resolution": "string"
}
```

---

## 11. Data Management

### Data Backup
```
POST /api/v1/data/backup
Body: {
  "type": "full|incremental",
  "tables": ["string"] // optional, for selective backup
}
Response: {
  "backupId": "string",
  "status": "initiated",
  "estimatedTime": "string"
}
```

### List Backups
```
GET /api/v1/data/backups
Response: {
  "backups": [{
    "id": "string",
    "type": "full|incremental",
    "size": "number",
    "status": "completed|failed|in_progress",
    "createdAt": "string",
    "downloadUrl": "string"
  }]
}
```

### Restore Data
```
POST /api/v1/data/restore
Body: {
  "backupId": "string",
  "tables": ["string"] // optional
}
```

### Export Data
```
POST /api/v1/data/export
Body: {
  "type": "users|hospitals|pharmacies|all",
  "format": "csv|xlsx|json",
  "filters": {...}
}
Response: {
  "exportId": "string",
  "downloadUrl": "string"
}
```

### Import Data
```
POST /api/v1/data/import
Body: FormData with file
Response: {
  "importId": "string",
  "status": "processing",
  "recordsProcessed": "number",
  "errors": ["string"]
}
```

---

## Error Handling

All endpoints should return consistent error responses:

```json
{
  "error": {
    "code": "ERROR_CODE",
    "message": "Human readable error message",
    "details": "Additional error details",
    "timestamp": "2024-01-01T00:00:00Z"
  }
}
```

Common HTTP status codes:
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

## Rate Limiting

Implement rate limiting for all endpoints:
- Authentication endpoints: 5 requests per minute
- Read operations: 100 requests per minute
- Write operations: 30 requests per minute
- Analytics endpoints: 20 requests per minute

## Notes for Implementation

1. **Authentication**: All endpoints except login/register require JWT authentication
2. **Pagination**: Use consistent pagination structure across all list endpoints
3. **Filtering**: Implement flexible filtering for list endpoints
4. **Validation**: Use proper input validation and sanitization
5. **Logging**: Log all admin actions and system events
6. **Caching**: Implement caching for frequently accessed data (analytics, settings)
7. **File Uploads**: Handle file uploads for profile pictures, documents, etc.
8. **Real-time Updates**: Consider WebSocket connections for real-time notifications
9. **Database**: Design efficient database schema with proper indexing
10. **Security**: Implement proper CORS, CSRF protection, and input sanitization

This specification provides a comprehensive foundation for building the backend API that will power the Nyla AI Command Center admin dashboard.

# Switch Role Feature - Complete Implementation

## ✅ What's Been Implemented

### 1. **Backend Integration** ✅
All existing backend endpoints are now accessible via API:

#### For Users with Both Roles (Simple Switch):
- `POST /api/role/switch` - Switch between contractor/owner views
- `GET /api/role/current` - Get current active role

#### For Users Adding a New Role (Multi-Step):
- `GET /api/role/switch-form` - Get form data (dropdowns, existing data)
- `POST /api/role/add/contractor/step1` - Step 1: Account info
- `POST /api/role/add/contractor/step2` - Step 2: Documents
- `POST /api/role/add/contractor/final` - Final: Complete registration
- `POST /api/role/add/owner/step1` - Step 1: Account info
- `POST /api/role/add/owner/step2` - Step 2: Documents  
- `POST /api/role/add/owner/final` - Final: Complete registration

### 2. **React Native UI** ✅
- `src/screens/both/switchRole.tsx` - Main switch role screen
- `src/services/role_service.ts` - API service layer

### 3. **Backend Updates** ✅
- All switch role methods now support **both Session and Sanctum** authentication
- Methods updated: `switchContractorStep1/2/Final`, `switchOwnerStep1/2/Final`, `showSwitchForm`

## 🎯 Current UI Behavior

### For Dual-Role Users (`user_type === 'both'`):
```
┌──────────────────────────────┐
│  ← Switch Role           │
├──────────────────────────────┤
│  CURRENT ROLE                │
│  ┌────────────────────────┐ │
│  │ 🏠 Property Owner      │ │
│  │     [ACTIVE]           │ │
│  └────────────────────────┘ │
│                              │
│  SWITCH TO                   │
│  ┌────────────────────────┐ │
│  │ 💼 Contractor       →  │ │
│  │ Tap to switch          │ │
│  └────────────────────────┘ │
└──────────────────────────────┘
```
✅ **Fully Working** - Simple one-tap role switch

### For Single-Role Users:
```
┌──────────────────────────────┐
│  ← Switch Role           │
├──────────────────────────────┤
│  CURRENT ROLE                │
│  ┌────────────────────────┐ │
│  │ 💼 Contractor  [ACTIVE]│ │
│  └────────────────────────┘ │
│                              │
│  ADD ANOTHER ROLE            │
│  ┌────────────────────────┐ │
│  │ 🏠 Property Owner    ➕ │ │
│  │ Tap to start registration│ │
│  └────────────────────────┘ │
└──────────────────────────────┘
```
⚠️ **Backend Ready** - UI shows message, form screens needed

## 📋 Next Steps for "Add Role" UI

To complete the "Add Role" feature, you need to create form screens similar to the signup flow:

### For Adding Contractor Role:
1. **Step 1 Screen**: Company information form
   - Company name, phone, experience, type, services, address
   - Uses: `POST /api/role/add/contractor/step1`

2. **Step 2 Screen**: Business documents form
   - PICAB number/category/expiration
   - Business permit details
   - TIN number
   - DTI/SEC registration photo
   - Uses: `POST /api/role/add/contractor/step2`

3. **Final Screen**: Profile picture (optional)
   - Uses: `POST /api/role/add/contractor/final`

### For Adding Owner Role:
1. **Step 1 Screen**: Account information (optional - can skip)
   - Username, email
   - Uses: `POST /api/role/add/owner/step1`

2. **Step 2 Screen**: Documents form
   - Valid ID selection and photos
   - Police clearance
   - Uses: `POST /api/role/add/owner/step2`

3. **Final Screen**: Personal information + Profile picture
   - First name, last name, DOB, phone, occupation, address
   - Uses: `POST /api/role/add/owner/final`

## 🔧 Backend Endpoints Reference

### Get Form Data:
```typescript
GET /api/role/switch-form
Response: {
  success: true,
  current_role: "contractor",
  existing_data: { ... },
  form_data: {
    contractor_types: [...],
    occupations: [...],
    valid_ids: [...],
    picab_categories: [...],
    provinces: [...]
  }
}
```

### Add Contractor Role:
```typescript
// Step 1
POST /api/role/add/contractor/step1
Body: {
  first_name?: string,
  middle_name?: string,
  last_name?: string,
  username?: string,
  company_email?: string
}

// Step 2
POST /api/role/add/contractor/step2
Body: FormData {
  picab_number: string,
  picab_category: string,
  picab_expiration_date: date,
  business_permit_number: string,
  business_permit_city: string,
  business_permit_expiration: date,
  tin_business_reg_number: string,
  dti_sec_registration_photo: File
}

// Final
POST /api/role/add/contractor/final
Body: FormData {
  profile_pic?: File
}
```

### Add Owner Role:
```typescript
// Step 1
POST /api/role/add/owner/step1
Body: {
  username?: string,
  email?: string
}

// Step 2
POST /api/role/add/owner/step2
Body: FormData {
  valid_id_id: number,
  valid_id_photo?: File,
  valid_id_back_photo?: File,
  police_clearance?: File
}

// Final
POST /api/role/add/owner/final
Body: FormData {
  first_name: string,
  middle_name?: string,
  last_name: string,
  date_of_birth: date,
  phone_number: string,
  occupation_id: number,
  occupation_other?: string,
  address: string,
  profile_pic?: File
}
```

## ✅ What's Working Now

1. ✅ Simple role switching for dual-role users
2. ✅ Current role detection
3. ✅ Backend endpoints accessible via API
4. ✅ Sanctum authentication support
5. ✅ UI shows proper options based on user type

## 🚧 What Needs UI Implementation

1. ⚠️ Multi-step form screens for "Add Role"
2. ⚠️ Form validation
3. ⚠️ File upload handling
4. ⚠️ Progress indicator
5. ⚠️ Success/error handling

## 📝 Notes

- All backend code uses existing `accountRequest` validation
- All backend code uses existing `accountClass` methods
- No database changes made
- Session storage used for web, but mobile can pass data directly in final step
- The backend accepts `step1_data` and `step2_data` in the final step for mobile compatibility

---

**Status**: Backend fully integrated ✅ | Simple switch working ✅ | Add Role UI needed ⚠️


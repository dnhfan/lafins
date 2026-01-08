# API Testing Guide - Postman Collection

Complete list of all API endpoints with request bodies and examples for testing in Postman.

**Base URL:** `http://your-domain.com/api`

---

## 1. Public Routes (No Authentication Required)

### 1.1 Register
- **Method:** `POST`
- **URL:** `/api/register`
- **Headers:**
  ```
  Content-Type: application/json
  Accept: application/json
  ```
- **Body (JSON):**
  ```json
  {
    "name": "John Doe",
    "email": "john@example.com",
    "password": "Password123!",
    "password_confirmation": "Password123!"
  }
  ```
- **Response (201):**
  ```json
  {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "token": "1|abc123..."
  }
  ```

### 1.2 Login
- **Method:** `POST`
- **URL:** `/api/login`
- **Headers:**
  ```
  Content-Type: application/json
  Accept: application/json
  ```
- **Body (JSON):**
  ```json
  {
    "email": "john@example.com",
    "password": "Password123!"
  }
  ```
- **Response (200):**
  ```json
  {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "token": "2|xyz789..."
  }
  ```

### 1.3 Forgot Password
- **Method:** `POST`
- **URL:** `/api/forgot-password`
- **Headers:**
  ```
  Content-Type: application/json
  Accept: application/json
  ```
- **Body (JSON):**
  ```json
  {
    "email": "john@example.com"
  }
  ```
- **Note:** Implementation pending in controller

### 1.4 Reset Password
- **Method:** `POST`
- **URL:** `/api/reset-password`
- **Headers:**
  ```
  Content-Type: application/json
  Accept: application/json
  ```
- **Body (JSON):**
  ```json
  {
    "token": "reset_token_here",
    "email": "john@example.com",
    "password": "NewPassword123!",
    "password_confirmation": "NewPassword123!"
  }
  ```
- **Note:** Implementation pending in controller

---

## 2. Protected Routes (Requires Authentication)

**⚠️ For all protected routes, add this header:**
```
Authorization: Bearer {your_token_from_login}
Accept: application/json
```

---

### 2.1 Auth & User Routes

#### 2.1.1 Get Current User
- **Method:** `GET`
- **URL:** `/api/user`
- **Headers:**
  ```
  Authorization: Bearer {token}
  Accept: application/json
  ```
- **Response (200):**
  ```json
  {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2024-01-01T00:00:00.000000Z"
  }
  ```

#### 2.1.2 Logout
- **Method:** `POST`
- **URL:** `/api/logout`
- **Headers:**
  ```
  Authorization: Bearer {token}
  Accept: application/json
  ```
- **Response (200):**
  ```json
  {
    "message": "Logged out successfully"
  }
  ```

---

### 2.2 Jars Management

#### 2.2.1 Get All Jars
- **Method:** `GET`
- **URL:** `/api/jars`
- **Headers:**
  ```
  Authorization: Bearer {token}
  Accept: application/json
  ```
- **Response (200):**
  ```json
  {
    "status": "success",
    "data": {
      "jars": [
        {
          "id": 1,
          "key": "NEC",
          "label": "Necessities",
          "percentage": 55.0,
          "balance": 5500.0
        },
        {
          "id": 2,
          "key": "FFA",
          "label": "Financial Freedom",
          "percentage": 10.0,
          "balance": 1000.0
        }
      ]
    }
  }
  ```

#### 2.2.2 Bulk Update Jars
- **Method:** `POST`
- **URL:** `/api/jars/bulk-update`
- **Headers:**
  ```
  Authorization: Bearer {token}
  Content-Type: application/json
  Accept: application/json
  ```
- **Body (JSON):**
  ```json
  {
    "percentages": {
      "1": 60,
      "2": 15,
      "3": 10,
      "4": 5,
      "5": 5,
      "6": 5
    }
  }
  ```
- **Note:** Keys are jar IDs, values are percentages (0-100)
- **Response (200):**
  ```json
  {
    "status": "success",
    "message": "Jar percentages is updated and balances"
  }
  ```

#### 2.2.3 Reset Jars to Default
- **Method:** `POST`
- **URL:** `/api/jars/reset`
- **Headers:**
  ```
  Authorization: Bearer {token}
  Accept: application/json
  ```
- **Body:** Empty
- **Response (200):**
  ```json
  {
    "status": "success",
    "message": "Jar percentages is reseted to default"
  }
  ```

#### 2.2.4 Delete All Jars Data
- **Method:** `POST`
- **URL:** `/api/jars/delete-all`
- **Headers:**
  ```
  Authorization: Bearer {token}
  Accept: application/json
  ```
- **Body:** Empty
- **Response (200):**
  ```json
  {
    "status": "success",
    "message": "Jar data is reset to default"
  }
  ```
- **Warning:** This deletes all incomes and outcomes!

---

### 2.3 Incomes Management

#### 2.3.1 List All Incomes
- **Method:** `GET`
- **URL:** `/api/incomes`
- **Headers:**
  ```
  Authorization: Bearer {token}
  Accept: application/json
  ```
- **Query Parameters (Optional):**
  - `range` - day, week, month, year, all
  - `start` - Start date (YYYY-MM-DD)
  - `end` - End date (YYYY-MM-DD)
  - `search` - Search in source/description
  - `sort_by` - Column to sort by (date, amount, source)
  - `sort_dir` - asc or desc
  - `page` - Page number
  - `per_page` - Items per page (default: 15)
- **Example:**
  ```
  /api/incomes?range=month&page=1&per_page=15
  ```
- **Response (200):**
  ```json
  {
    "incomes": {
      "data": [
        {
          "id": 1,
          "date": "2024-01-15",
          "source": "Salary",
          "description": "Monthly salary",
          "amount": 10000,
          "formatted_amount": "$10,000.00"
        }
      ],
      "current_page": 1,
      "per_page": 15,
      "total": 50
    },
    "filters": {
      "range": "month",
      "page": 1
    }
  }
  ```

#### 2.3.2 Create Income
- **Method:** `POST`
- **URL:** `/api/incomes`
- **Headers:**
  ```
  Authorization: Bearer {token}
  Content-Type: application/json
  Accept: application/json
  ```
- **Body (JSON):**
  ```json
  {
    "date": "2024-01-15",
    "source": "Freelance Work",
    "description": "Web development project",
    "amount": 5000
  }
  ```
- **Required Fields:** `date`, `source`, `amount`
- **Optional Fields:** `description`
- **Response (201):**
  ```json
  {
    "status": "success",
    "message": "Added and splited to jars.",
    "data": {
      "incomes": {
        "id": 5,
        "date": "2024-01-15",
        "source": "Freelance Work",
        "description": "Web development project",
        "amount": 5000
      },
      "filters": {
        "range": "day",
        "page": 1
      }
    }
  }
  ```

#### 2.3.3 Get Single Income
- **Method:** `GET`
- **URL:** `/api/incomes/{id}`
- **Headers:**
  ```
  Authorization: Bearer {token}
  Accept: application/json
  ```
- **Example:** `/api/incomes/5`

#### 2.3.4 Update Income
- **Method:** `PUT` or `PATCH`
- **URL:** `/api/incomes/{id}`
- **Headers:**
  ```
  Authorization: Bearer {token}
  Content-Type: application/json
  Accept: application/json
  ```
- **Body (JSON):**
  ```json
  {
    "date": "2024-01-16",
    "source": "Freelance Work Updated",
    "description": "Updated description",
    "amount": 5500
  }
  ```
- **Example:** `/api/incomes/5`
- **Response (200):**
  ```json
  {
    "status": "success",
    "message": "Updated income!",
    "data": {
      "income": {
        "id": 5,
        "date": "2024-01-16",
        "source": "Freelance Work Updated",
        "amount": 5500
      },
      "filler": {
        "range": "day",
        "page": 1
      }
    }
  }
  ```

#### 2.3.5 Delete Income
- **Method:** `DELETE`
- **URL:** `/api/incomes/{id}`
- **Headers:**
  ```
  Authorization: Bearer {token}
  Accept: application/json
  ```
- **Example:** `/api/incomes/5`
- **Response (200):**
  ```json
  {
    "status": "success",
    "message": "Deleted and updated jar splits"
  }
  ```

---

### 2.4 Outcomes Management

#### 2.4.1 List All Outcomes
- **Method:** `GET`
- **URL:** `/api/outcomes`
- **Headers:**
  ```
  Authorization: Bearer {token}
  Accept: application/json
  ```
- **Query Parameters (Optional):**
  - `range` - day, week, month, year, all
  - `start` - Start date (YYYY-MM-DD)
  - `end` - End date (YYYY-MM-DD)
  - `search` - Search in category/description
  - `sort_by` - Column to sort by (date, amount, category)
  - `sort_dir` - asc or desc
  - `page` - Page number
  - `per_page` - Items per page (default: 15)
- **Example:**
  ```
  /api/outcomes?range=week&page=1
  ```
- **Response (200):**
  ```json
  {
    "outcomes": {
      "data": [
        {
          "id": 1,
          "date": "2024-01-15",
          "category": "Food",
          "description": "Grocery shopping",
          "amount": 200,
          "jar_id": 1,
          "jar_label": "NEC",
          "formatted_amount": "$200.00"
        }
      ],
      "current_page": 1,
      "per_page": 15,
      "total": 30
    },
    "jars": [
      {
        "id": 1,
        "name": "NEC",
        "percentage": 55,
        "balance": 5300
      }
    ],
    "filters": {
      "range": "week",
      "page": 1
    }
  }
  ```

#### 2.4.2 Create Outcome
- **Method:** `POST`
- **URL:** `/api/outcomes`
- **Headers:**
  ```
  Authorization: Bearer {token}
  Content-Type: application/json
  Accept: application/json
  ```
- **Body (JSON):**
  ```json
  {
    "date": "2024-01-15",
    "category": "Transportation",
    "description": "Gas",
    "amount": 50,
    "jar_id": 1
  }
  ```
- **Required Fields:** `date`, `category`, `amount`
- **Optional Fields:** `description`, `jar_id`
- **Response (201):**
  ```json
  {
    "status": "success",
    "message": "Added outcome!",
    "data": {
      "id": 10,
      "date": "2024-01-15",
      "category": "Transportation",
      "description": "Gas",
      "amount": 50,
      "jar_id": 1
    }
  }
  ```

#### 2.4.3 Get Single Outcome
- **Method:** `GET`
- **URL:** `/api/outcomes/{id}`
- **Headers:**
  ```
  Authorization: Bearer {token}
  Accept: application/json
  ```
- **Example:** `/api/outcomes/10`

#### 2.4.4 Update Outcome
- **Method:** `PUT` or `PATCH`
- **URL:** `/api/outcomes/{id}`
- **Headers:**
  ```
  Authorization: Bearer {token}
  Content-Type: application/json
  Accept: application/json
  ```
- **Body (JSON):**
  ```json
  {
    "date": "2024-01-16",
    "category": "Transportation",
    "description": "Gas and parking",
    "amount": 75,
    "jar_id": 1
  }
  ```
- **Example:** `/api/outcomes/10`
- **Response (200):**
  ```json
  {
    "status": "success",
    "message": "Updated outcomes"
  }
  ```

#### 2.4.5 Delete Outcome
- **Method:** `DELETE`
- **URL:** `/api/outcomes/{id}`
- **Headers:**
  ```
  Authorization: Bearer {token}
  Accept: application/json
  ```
- **Example:** `/api/outcomes/10`
- **Response (200):**
  ```json
  {
    "status": "success",
    "message": "Deleted outcomes!"
  }
  ```

---

## 3. Testing Workflow

### Step 1: Register & Login
1. Use **Register** to create a new account
2. Copy the `token` from the response
3. Or use **Login** if account exists

### Step 2: Set Authorization
In Postman, for all protected endpoints:
- Go to **Authorization** tab
- Select **Bearer Token**
- Paste your token

### Step 3: Test Jars
1. **GET** `/api/jars` - View your jars
2. **POST** `/api/jars/bulk-update` - Adjust percentages
3. **POST** `/api/jars/reset` - Reset to defaults

### Step 4: Test Incomes
1. **POST** `/api/incomes` - Create an income (automatically splits to jars)
2. **GET** `/api/incomes` - List all incomes
3. **PUT** `/api/incomes/{id}` - Update an income
4. **DELETE** `/api/incomes/{id}` - Delete (reverses jar splits)

### Step 5: Test Outcomes
1. **POST** `/api/outcomes` - Create an outcome (deducts from jar)
2. **GET** `/api/outcomes` - List all outcomes
3. **PUT** `/api/outcomes/{id}` - Update an outcome
4. **DELETE** `/api/outcomes/{id}` - Delete (refunds jar)

### Step 6: Logout
- **POST** `/api/logout` - Invalidates the current token

---

## 4. Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "status": "error",
  "message": "You do not have permission to do this!"
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password field is required."]
  }
}
```

### 400 Bad Request
```json
{
  "status": "error",
  "message": "Insufficient balance in selected jar."
}
```

---

## 5. Postman Environment Variables

Create a Postman environment with these variables:

```
base_url: http://localhost:8000
api_url: {{base_url}}/api
token: (will be set after login)
```

Then use `{{api_url}}/login` in your requests.

---

## 6. Notes

- All amounts are in integer format (cents/smallest currency unit)
- Dates should be in `YYYY-MM-DD` format
- Income creation automatically distributes to jars based on percentages
- Outcome creation deducts from selected jar (if provided)
- Deleting income reverses jar allocations
- Deleting outcome refunds jar balance
- Token expires based on Sanctum configuration

---

**Total Endpoints: 20**

✅ Ready to test!

# API Documentation

## Authentication Endpoints

### POST /api/auth/register
Register a new user account.

**Request:**
- **Method:** POST
- **Content-Type:** `application/json` or `multipart/form-data`

**Input (JSON):**
```json
{
  "username": "string (required, max 100 chars)",
  "email": "string (required, max 100 chars, unique)",
  "password": "string (required, SHA-256 hashed)",
  "confirmPassword": "string (required, must match password)",
  "telepon": "string (required, max 15 chars)",
  "alamat": "string (required)"
}
```

**Input (Form Data):**
- `username` - string (required, max 100 chars)
- `email` - string (required, max 100 chars, unique)
- `password` - string (required, SHA-256 hashed)
- `confirmPassword` - string (required, must match password)
- `telepon` - string (required, max 15 chars)
- `alamat` - string (required)
- `foto` - file (optional, max 16MB, JPG/PNG/GIF only)

**Output (Success - 201):**
```json
{
  "status": 201,
  "success": true,
  "message": "User registered successfully.",
  "user_id": 123
}
```

**Output (Validation Error - 400):**
```json
{
  "status": 400,
  "success": false,
  "error": "Input validation failed",
  "details": [
    "Username is required.",
    "Password must be at least 8 characters long."
  ]
}
```

**Output (User Exists - 409):**
```json
{
  "status": 409,
  "success": false,
  "error": "A user with this email or username already exists."
}
```

**Output (Server Error - 500):**
```json
{
  "status": 500,
  "success": false,
  "error": "An unexpected error occurred.",
  "details": "error message"
}
```

---

### POST /api/auth/login
Authenticate a user and generate a session token.

**Request:**
- **Method:** POST
- **Content-Type:** `application/json` or `application/x-www-form-urlencoded`

**Input:**
```json
{
  "email": "string (optional if username provided)",
  "username": "string (optional if email provided)",
  "password": "string (required, SHA-256 hashed)"
}
```

**Note:** You can provide either `email` OR `username` for authentication.

**Output (Success - 200):**
```json
{
  "status": 200,
  "session_token": "64-character hex string",
  "expires_at": "2025-12-03 12:34:56"
}
```

**Output (Validation Error - 400):**
```json
{
  "status": 400,
  "error": "Email/username and password are required."
}
```

**Output (Invalid Credentials - 401):**
```json
{
  "status": 401,
  "error": "Invalid email or password."
}
```

**Output (Server Error - 500):**
```json
{
  "status": 500,
  "error": "Database error: error message"
}
```

---

## Important Notes

### Password Hashing
- All passwords must be **SHA-256 hashed** on the client side before sending to the API
- The server stores and compares SHA-256 hashes directly
- Do NOT send plain-text passwords

### Session Management
- Upon successful login, a session token is generated (64-character hex string)
- Session tokens expire after 24 hours
- Store the session token securely (localStorage or cookies)
- Include the session token in subsequent API requests for authentication

### User Session Table
Sessions are stored with the following information:
- `id_user` - User ID
- `session_token` - Unique session identifier
- `ip_address` - Client IP address
- `expires_at` - Expiration timestamp
- `created_at` - Creation timestamp

---

## Example Usage

### Register Example (cURL)
```bash
curl -X POST http://localhost:80/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "username": "John Doe",
    "email": "john@example.com",
    "password": "5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8",
    "confirmPassword": "5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8",
    "telepon": "081234567890",
    "alamat": "Jl. Example No. 123"
  }'
```

### Login Example (cURL)
```bash
# Login with email
curl -X POST http://localhost:80/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8"
  }'

# Login with username
curl -X POST http://localhost:80/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "John Doe",
    "password": "5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8"
  }'
```

### JavaScript Example
```javascript
// SHA-256 hash function
async function sha256(message) {
  const msgBuffer = new TextEncoder().encode(message);
  const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
  const hashArray = Array.from(new Uint8Array(hashBuffer));
  return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
}

// Register
async function register(username, email, password, telepon, alamat) {
  const hashedPassword = await sha256(password);
  
  const response = await fetch('http://localhost:80/api/auth/register', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      username,
      email,
      password: hashedPassword,
      confirmPassword: hashedPassword,
      telepon,
      alamat
    })
  });
  
  return await response.json();
}

// Login
async function login(identifier, password) {
  const hashedPassword = await sha256(password);
  
  // Auto-detect if identifier is email or username
  const isEmail = identifier.includes('@');
  const payload = {
    password: hashedPassword
  };
  
  if (isEmail) {
    payload.email = identifier;
  } else {
    payload.username = identifier;
  }
  
  const response = await fetch('http://localhost:80/api/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });
  
  return await response.json();
}
```

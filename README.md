# 🔧 Student Management API

A RESTful API built with PHP and MySQL for managing student records. This backend powers the Student Management Android App with secure authentication and complete CRUD operations.

## 🌐 Live API

**Base URL:** `https://student-management-api-production-4e55.up.railway.app/`

## 🎯 Features

- ✅ User Authentication (Login)
- ✅ Get All Students
- ✅ Add New Student
- ✅ Update Student Details
- ✅ Delete Student
- ✅ CORS Enabled for Mobile Access
- ✅ JSON Response Format
- ✅ MySQL Database Integration

## 🛠️ Tech Stack

- **Backend:** PHP 8.x
- **Database:** MySQL
- **Hosting:** Railway
- **API Format:** RESTful JSON

## 📁 Project Structure
```
student-management-api/
├── api/
│   ├── login.php           # User authentication
│   ├── students.php        # Get all students
│   ├── add_student.php     # Add new student
│   ├── update_student.php  # Update student
│   └── delete_student.php  # Delete student
├── config/
│   └── database.php        # Database configuration
├── database/
│   └── schema.sql          # Database schema
├── .htaccess               # Apache configuration
└── README.md
```

## 🗄️ Database Schema

### Users Table
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Students Table
```sql
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    class VARCHAR(50) NOT NULL,
    roll_no VARCHAR(50) UNIQUE NOT NULL,
    contact VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## 📡 API Endpoints

### 1. Login
**Endpoint:** `POST /api/login.php`

**Request:**
```json
{
    "email": "admin@example.com",
    "password": "admin123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "Admin",
        "email": "admin@example.com"
    }
}
```

---

### 2. Get All Students
**Endpoint:** `GET /api/students.php`

**Response:**
```json
{
    "success": true,
    "students": [
        {
            "id": 1,
            "name": "John Doe",
            "class": "10th",
            "roll_no": "2024001",
            "contact": "9876543210"
        }
    ]
}
```

---

### 3. Add Student
**Endpoint:** `POST /api/add_student.php`

**Request:**
```json
{
    "name": "John Doe",
    "class": "10th",
    "roll_no": "2024001",
    "contact": "9876543210"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Student added successfully",
    "student_id": 1
}
```

---

### 4. Update Student
**Endpoint:** `PUT /api/update_student.php`

**Request:**
```json
{
    "id": 1,
    "name": "John Doe Updated",
    "class": "11th",
    "roll_no": "2024001",
    "contact": "9876543210"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Student updated successfully"
}
```

---

### 5. Delete Student
**Endpoint:** `DELETE /api/delete_student.php`

**Request:**
```json
{
    "id": 1
}
```

**Response:**
```json
{
    "success": true,
    "message": "Student deleted successfully"
}
```

## 🚀 Local Setup

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx server
- Composer (optional)

### Installation Steps

1. **Clone the repository**
```bash
git clone https://github.com/Cool-Developer-Nigam/student-management-api.git
cd student-management-api
```

2. **Create MySQL Database**
```sql
CREATE DATABASE student_management;
```

3. **Import Database Schema**
```bash
mysql -u root -p student_management < database/schema.sql
```

4. **Configure Database Connection**

Edit `config/database.php`:
```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'student_management');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
?>
```

5. **Start Local Server**
```bash
php -S localhost:8000
```

6. **Test API**
```bash
curl http://localhost:8000/api/students.php
```

## 🌍 Railway Deployment

### Environment Variables
Set these in Railway dashboard:
```
MYSQLHOST=your_host
MYSQLPORT=3306
MYSQLDATABASE=railway
MYSQLUSER=root
MYSQLPASSWORD=your_password
```

### Deploy Steps
1. Connect GitHub repository to Railway
2. Set environment variables
3. Deploy automatically on push
4. Access via provided Railway URL

## 🔒 Security Features

- ✅ Password hashing using `password_hash()`
- ✅ Prepared statements to prevent SQL injection
- ✅ Input validation and sanitization
- ✅ CORS headers for secure cross-origin requests
- ✅ Error handling without exposing sensitive data

## 🧪 Testing

### Using cURL

**Test Login:**
```bash
curl -X POST https://student-management-api-production-4e55.up.railway.app/api/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"admin123"}'
```

**Test Get Students:**
```bash
curl https://student-management-api-production-4e55.up.railway.app/api/students.php
```

**Test Add Student:**
```bash
curl -X POST https://student-management-api-production-4e55.up.railway.app/api/add_student.php \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","class":"10th","roll_no":"2024001","contact":"9876543210"}'
```

### Using Postman
1. Import the API endpoints
2. Set base URL
3. Test each endpoint with sample data
4. Verify responses

## 📝 Default Credentials

For testing purposes:
```
Email: admin@example.com
Password: admin123
```

**⚠️ Important:** Change these credentials in production!

## 🐛 Error Handling

All endpoints return consistent error format:
```json
{
    "success": false,
    "message": "Error description here"
}
```

Common HTTP Status Codes:
- `200` - Success
- `400` - Bad Request
- `401` - Unauthorized
- `404` - Not Found
- `500` - Server Error

## 📊 API Response Format

All responses follow this structure:
```json
{
    "success": boolean,
    "message": "string",
    "data": object/array (optional)
}
```

## 🔄 CORS Configuration

CORS is enabled for all origins. In `.htaccess`:
```apache
Header set Access-Control-Allow-Origin "*"
Header set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
Header set Access-Control-Allow-Headers "Content-Type, Authorization"
```

## 📈 Future Enhancements

- [ ] JWT Authentication
- [ ] Rate Limiting
- [ ] API Documentation with Swagger
- [ ] File Upload for Student Photos
- [ ] Batch Operations
- [ ] Search and Filter
- [ ] Pagination
- [ ] API Versioning

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License.

## 👨‍💻 Author

**[Your Name]**
- GitHub: [@Cool-Developer-Nigam](https://github.com/Cool-Developer-Nigam)
- Email: [Your Email]

## 🔗 Related Projects

- **Android App:** [Student Management App](https://github.com/Cool-Developer-Nigam/Aone_project_demo)

## 📞 Support

For issues or questions:
- Open an issue on GitHub
- Contact: [Your Email]

---

⭐ If you found this helpful, please star the repository!

---

**Made with ❤️ for the Student Management System**

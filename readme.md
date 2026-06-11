PharmaFEFO - Pharmacy Stock Management (FEFO)
PharmaFEFO is a lightweight PHP OOP MVC web application designed for pharmacy stock management. It implements the FEFO (First Expired First Out) method, optimizing medicine stock dispatching to prevent waste due to product expiration.

🚀 Features
Custom OOP MVC Architecture: Lightweight custom router, front-controller setup, and modular class structure.
Dynamic Seeding & Sync: Self-seeding database setup and real-time expiration warning updates.
Role-Based Access Control:
Admin: Can perform all actions.
Pharmacist: Views medicine listings and dispenses stock.
Stock Manager: Manages medicines and batch details.
FEFO Dispatch Algorithm: Automatically selects the nearest expiring batch (ignoring expired and empty batches) to fulfill dispense requests.
Color-Coded Alert Badges:
Green: More than 90 days left.
Orange: Between 30 and 90 days left.
Red: Less than 30 days left.
Expired: Past expiration date.
Polished Responsive Interface: Styled using TailwindCSS with a clean sidebar navigation.
🛠️ Technology Stack
Backend: PHP 8.0+ (using PDO and native sessions)
Database: MySQL
Frontend: HTML5, Vanilla CSS, TailwindCSS (via Play CDN), Vanilla JS
Design Pattern: Object-Oriented MVC
📂 Project Structure
text

pharmafefo/
├── config/
│   └── Database.php             # PDO database connection configuration
├── models/
│   ├── User.php                 # Authentication & auto-seeding
│   ├── Medicine.php             # Medicine CRUD
│   ├── Batch.php                # Batch CRUD & FEFO algorithm
│   └── Alert.php                # Expiration alert sync & warnings
├── controllers/
│   ├── AuthController.php       # Handles user sessions & login
│   ├── DashboardController.php  # Handles metrics computations
│   ├── MedicineController.php   # Handles medicine CRUD & dispensing
│   ├── BatchController.php      # Handles batch CRUD & validations
│   └── AlertController.php      # Handles expiration warnings view
├── views/
│   ├── layouts/
│   │   └── layout.php           # Global template wrapper (sidebar, headers)
│   ├── auth/
│   │   └── login.php            # Glassmorphic login page
│   ├── dashboard/
│   │   └── index.php            # Visual statistics & alerts summary
│   ├── medicines/
│   │   ├── list.php             # Medicine list, search & dispense modal
│   │   ├── create.php           # Add medicine form
│   │   └── edit.php             # Edit medicine form
│   ├── batches/
│   │   ├── list.php             # Batch details & colored indicators
│   │   ├── create.php           # Add batch form
│   │   └── edit.php             # Edit batch form
│   ├── alerts/
│   │   └── list.php             # Full expiration warnings table
│   └── 404.php                  # Friendly route failure page
├── public/
│   └── index.php                # Front controller / Router
└── database/
    └── pharmafefo.sql           # Database schema & sample seed data
⚙️ Installation & Setup
1. Database Setup
Open your MySQL client (e.g. phpMyAdmin, MySQL Workbench, CLI).
Import the schema script located at database/pharmafefo.sql.
This creates a database called pharmafefo and populates sample medicines and batches.
2. Configuration
Open 
Database.php
 and confirm the database connection details match your MySQL instance:

php

private $host = "localhost";
private $dbname = "pharmafefo";
private $username = "root";     // Your MySQL username
private $password = "";         // Your MySQL password
3. Running the Server
In your terminal, navigate to the pharmafefo/ directory and start PHP's built-in web server:

bash

php -S localhost:8000 -t public
Now, navigate to http://localhost:8000 in your browser.

🔐 Default Login Credentials
For ease of testing, the application automatically seeds three default accounts on first load:

Role	Email	Password
Admin	admin@pharmafefo.com	password123
Pharmacist	pharmacist@pharmafefo.com	password123
Stock Manager	manager@pharmafefo.com	password123
(You can select these instantly on the login page using the quick-filled shortcut buttons).

🔬 FEFO Algorithm Core Logic
The core selection query is implemented inside 
Batch.php
 under getFefoBatch($medicineId):

php

public function getFefoBatch($medicineId)
{
    $query = "SELECT * FROM batches
              WHERE medicine_id = :medicine_id 
                AND quantity > 0 
                AND expiration_date >= CURRENT_DATE() 
              ORDER BY expiration_date ASC 
              LIMIT 1";
    $stmt = $this->db->prepare($query);
    $stmt->execute([':medicine_id' => $medicineId]);
    return $stmt->fetch();
}
When dispensing, the method dispense($medicineId, $quantityToDispense) utilizes a database transaction. It fetches all active unexpired batches sorted ascending by date (FEFO) and deducts stock from them sequentially until the quantity requested is fully satisfied:

php

foreach ($batches as $batch) {
    if ($remaining <= 0) break;
    if ($batch['quantity'] >= $remaining) {
        $newQty = $batch['quantity'] - $remaining;
        // Update batch quantity
        $remaining = 0;
    } else {
        $remaining -= $batch['quantity'];
        // Set batch quantity to 0
    }
}
If the total stock across all active unexpired batches is insufficient, the transaction automatically rolls back to maintain database consistency.
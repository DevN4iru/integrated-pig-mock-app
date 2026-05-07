# 🐖 PigStep - Pig Management System

PigStep is a web-based Pig Management Information System (MIS) built using Laravel and Docker. It is designed to manage pig records, farm operations, and related data in a structured and scalable way.

## 🚀 Tech Stack
- Backend: Laravel (PHP 8.2)  
- Web Server: Nginx  
- Database: MariaDB 10.6  
- Containerization: Docker & Docker Compose  

## 📦 Project Structure
pigstep/  
├── docker/  
│   ├── php/  
│   │   └── Dockerfile  
│   └── nginx/  
│       └── default.conf  
├── src/ (Laravel application)  
├── docker-compose.yml  
├── .env.example  
└── README.md  

## ⚙️ Prerequisites
- Docker  
- Docker Compose  
- Git  

## 🛠️ Setup Instructions

1. Clone the repository  
git clone <your-repo-url>  
cd pigstep  

2. Copy environment file  
cp .env.example .env  

3. Build and start containers  
docker compose up -d --build  

4. Generate Laravel application key  
docker exec -it pigstep_app php artisan key:generate  

5. Run database migrations  
docker exec -it pigstep_app php artisan migrate  

## 🌐 Access the Application  
Open your browser and go to:  
http://localhost:8000  

## 🗄️ Database Configuration (.env)
DB_CONNECTION=mysql  
DB_HOST=db  
DB_PORT=3306  
DB_DATABASE=pigstep  
DB_USERNAME=user  
DB_PASSWORD=sample_password

## 🧪 Useful Commands

Enter the app container:  
docker exec -it pigstep_app bash  

Stop containers:  
docker compose down  

Stop and remove volumes (reset database):  
docker compose down -v  

## 👥 Collaboration Guide
1. Clone the repository  
2. Copy .env.example to .env  
3. Run docker compose up -d --build  
4. Generate app key  
5. Run migrations  

## ⚠️ Notes
- Do not commit the .env file  
- Use .env.example as a template  
- Ensure Docker is running before starting containers  
- Database data is stored in Docker volumes  

## 📌 Planned Features
- Pig records management (CRUD)  
- Breeding tracking  
- Health monitoring  
- Feeding logs  
- Inventory management  
- Reports and analytics  

## 🧠 License
This project is licensed under the MIT License.

---

## Client-Guided Handoff Notes

This branch is the simplified client handoff branch.

### Official dashboard rule

Official Net Position = Sold Profit - Mortality Loss.

Active pigs are not treated as official money. Reference costs are shown separately for visibility only.

### Client simplifications completed

- Batch Sell uses one actual total sale price.
- Batch Sell no longer uses recommended value, current pig value, or pricing mode.
- Register Born Piglets no longer asks for Farm Value.
- Active born piglets are not counted as money until actual sale.
- Archived pigs can be restored normally.
- Permanent delete remains protected when linked records exist.

### Conditional workflows

- Breeding starts only from valid female pigs.
- Not-ready female pigs are separated from ready breeding candidates.
- Pregnancy check appears only when the breeding case is serviced.
- Returned to Heat appears only after a not-pregnant result.
- Start Next Attempt appears only after returned-to-heat status.
- Register Born Piglets appears only after valid farrowing with born-alive piglets.
- Medication program appears only for eligible piglets or lactating sows.
- Sold, dead, and archived pigs are locked from normal active-farm actions.

### Final smoke test before client submission

1. Dashboard loads correctly.
2. Pig List loads correctly.
3. Batch Sell updates Sold Profit and Net Position.
4. Register Born Piglets has no Farm Value field.
5. Archive and Restore work.
6. Permanent Delete stays blocked for pigs with linked records.


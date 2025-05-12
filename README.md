
# 🧵 YoPrint Laravel Coding Assessment

This is my submission for the **YoPrint Laravel Coding Project**, built to meet the project specification. The goal of this project is to create a robust, production-ready Laravel application with real-time capabilities and an efficient, user-friendly interface.

---

## 🚀 Tech Stack

- **Laravel** – backend framework
- **Blade** – templating engine
- **Tailwind CSS** – styling
- **Laravel Reverb + Echo** – real-time communication
- **DataTables** – server-side table rendering
- **Laravel Horizon** – queue monitoring
- **Redis** – queue and broadcast driver

---

## ✅ What’s Implemented

- 🟢 **Real-Time Job Updates** – via Laravel Echo + Reverb
- 📦 **Queued Background Processing** – using Redis + Horizon
- 🎨 **Responsive UI** – styled with Tailwind CSS
- 📑 **Clean Blade Templates** – modular and reusable
- 🔁 **Live Broadcasting Events** – job status and progress updates

---

## 🛠️ Getting Started

Follow these steps to set up the project locally.

### 1. Clone the Repo
```bash
git clone https://github.com/asaifulas/YoPrintAssessment.git
cd yoprint-assessment
```

### 2. Install Dependencies
```bash
composer install
npm install && npm run dev
```

### 3. Configure Environment
```bash
cp .env.example .env
php artisan key:generate
```

Update your `.env` file with:

- Database credentials
- Redis configuration
- Broadcasting driver: `BROADCAST_DRIVER=reverb`

### 4. Migrate & Seed Database
```bash
php artisan migrate --seed
```

### 5. Start Services
In separate terminals, run:

```bash
php artisan serve        # Start Laravel server
php artisan horizon      # Start Horizon dashboard
php artisan reverb:start # Start Reverb server
npm run dev              # Start Vite
```

---

## 📌 Notes

- **Laravel Reverb** is used instead of external services like Pusher.
- **Queues** are processed via Redis and monitored using **Horizon**.
- The code follows **Laravel best practices**, is modular, and easy to extend.
- Powered by ChatGPT

---

## 🧑‍💻 Author

**Your Name**  
GitHub: [@asaifulas](https://github.com/asaifulas)  
Email: asaifulas@gmail.com

---

## 📄 License

This project is for assessment purposes only and is not licensed for commercial use.

const express = require("express");
const { Pool } = require("pg");
const app = express();
const port = 3001; // Port the backend listens on

// PostgreSQL connection pool
const pool = new Pool({
  user: process.env.DB_USER,
  host: process.env.DB_HOST,
  database: process.env.DB_NAME,
  password: process.env.DB_PASSWORD,
  port: process.env.DB_PORT,
});

// Simple health check endpoint for probes
app.get("/healthz", (req, res) => {
  // Optional: Add a quick DB check here if needed
  res.status(200).send("OK");
});

// API endpoint to get data from DB
app.get("/api/data", async (req, res) => {
  try {
    const result = await pool.query("SELECT id, name FROM items ORDER BY id");
    res.json(result.rows);
  } catch (err) {
    console.error("Error fetching data from DB:", err);
    res.status(500).send("Error fetching data");
  }
});

// Basic root response
app.get("/", (req, res) => {
  res.send("Backend API is running!");
});

app.listen(port, () => {
  console.log(`Backend API listening on port ${port}`);
});

const express = require("express");
const { Pool } = require("pg");
const app = express();
const port = process.env.SERVER_PORT || 3001; // Use environment variable or default
const apiHost = process.env.API_HOST || "0.0.0.0"; // Default to all available network interfaces

// PostgreSQL connection pool using environment variables set via ConfigMaps and Secrets
const pool = new Pool({
  user: process.env.DB_USER, // Expected to be set in a Secret
  host: process.env.DB_HOST, // Expected to be set in a ConfigMap
  database: process.env.DB_NAME, // Expected to be set in a ConfigMap
  password: process.env.DB_PASSWORD, // Expected to be set in a Secret
  port: process.env.DB_PORT || 5432, // Default PostgreSQL port
});

// Health check endpoint for Kubernetes probes
app.get("/healthcheck", async (req, res) => {
  try {
    await pool.query("SELECT 1"); // Quick DB check
    res.status(200).send("OK");
  } catch (error) {
    console.error("Database health check failed:", error);
    res.status(500).send("Database unavailable");
  }
});

// API endpoint to get inventory items
app.get("/api/inventory", async (req, res) => {
  try {
    const result = await pool.query(
      "SELECT item_id, item_name, price, quantity FROM inventory ORDER BY item_id"
    );
    res.json(result.rows);
  } catch (err) {
    console.error("Error fetching inventory data:", err);
    res.status(500).send("Error fetching data");
  }
});

// Root response
app.get("/", (req, res) => {
  res.send("Backend API for E-Commerce Inventory is running!");
});

app.listen(port, apiHost, () => {
  console.log(`Backend API listening on port ${port}`);
});

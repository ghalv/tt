<?php
include '../includes/db_connect.php';

$db->exec("CREATE TABLE IF NOT EXISTS Users (
    user_id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    role TEXT NOT NULL
)");

$db->exec("CREATE TABLE IF NOT EXISTS Projects (
    project_id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    customer TEXT NOT NULL
)");

$db->exec("CREATE TABLE IF NOT EXISTS WeeklyAllocations (
    allocation_id INTEGER PRIMARY KEY AUTOINCREMENT,
    week_number INTEGER NOT NULL,
    user_id INTEGER,
    project_id INTEGER,
    allocated_percentage INTEGER CHECK(allocated_percentage % 5 = 0), 
    UNIQUE(week_number, user_id, project_id),
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (project_id) REFERENCES Projects(project_id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS WeeklyDemand (
    week_number INTEGER NOT NULL,
    project_id INTEGER NOT NULL,
    demand_percentage INTEGER CHECK(demand_percentage >= 0 AND demand_percentage <= 300),
    FOREIGN KEY (project_id) REFERENCES Projects(project_id),
    UNIQUE(week_number, project_id)
)");

echo "Tables created successfully!";

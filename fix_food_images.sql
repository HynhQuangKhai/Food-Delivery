-- Fix broken image URLs in food_items table
-- This clears corrupted base64 data and sets proper image URLs

-- Step 1: Clear all corrupted base64 image data
UPDATE food_items SET image_url = NULL;

-- Step 2: Set proper food images by ID (using Unsplash free food images)
-- You can replace these URLs with your own images

-- Burgers
UPDATE food_items SET image_url = 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400' WHERE id = 1;
UPDATE food_items SET image_url = 'https://images.unsplash.com/photo-1553979459-d2229ba7433b?w=400' WHERE id = 4;

-- Pizza
UPDATE food_items SET image_url = 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=400' WHERE id = 2;

-- Salad
UPDATE food_items SET image_url = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400' WHERE id IN (5, 6);

-- Pasta/Spaghetti
UPDATE food_items SET image_url = 'https://images.unsplash.com/photo-1621996346565-e3dbc646d9a9?w=400' WHERE id IN (7, 13, 19);
UPDATE food_items SET image_url = 'https://images.unsplash.com/photo-1556761223-4c4282c73f77?w=400' WHERE id = 8;

-- Japanese/Sushi
UPDATE food_items SET image_url = 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?w=400' WHERE id IN (9, 16);

-- Tempura/Fried
UPDATE food_items SET image_url = 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=400' WHERE id = 10;

-- Chicken dishes
UPDATE food_items SET image_url = 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=400' WHERE id = 11;

-- Steak/Beef
UPDATE food_items SET image_url = 'https://images.unsplash.com/photo-1600891964092-4316c288032e?w=400' WHERE id IN (12, 15);

-- Default placeholder for any remaining items without images
-- UPDATE food_items SET image_url = 'images/placeholder.jpg' WHERE image_url IS NULL;

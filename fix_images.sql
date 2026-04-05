-- Fix broken image URLs in food_items table
-- This clears invalid URLs so placeholder image will show

-- Clear all image_urls (will show placeholder.jpg)
UPDATE food_items SET image_url = NULL;

-- OR: Set default food images by category
-- UPDATE food_items SET image_url = 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400' WHERE category = 'Burger';
-- UPDATE food_items SET image_url = 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=400' WHERE category = 'Pizza';
-- UPDATE food_items SET image_url = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400' WHERE category = 'Salad';

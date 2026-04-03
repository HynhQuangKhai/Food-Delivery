-- Add image_url column to food_items table
ALTER TABLE food_items ADD COLUMN image_url VARCHAR(500) DEFAULT NULL;

-- Update existing food items with sample image URLs
UPDATE food_items SET image_url = 'https://www.themealdb.com/images/media/meals/x0lk931587671540.jpg' WHERE id = 1;
UPDATE food_items SET image_url = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTHA83KUkZL_J-d9CZ8t6MOJ_G7p-svPKKn0g&s' WHERE id = 2;
UPDATE food_items SET image_url = 'https://www.themealdb.com/images/media/meals/urzj1d1587670726.jpg' WHERE id = 3;
UPDATE food_items SET image_url = 'https://www.recipetineats.com/tachyon/2023/09/Crispy-fried-chicken-burgers_5.jpg' WHERE id = 4;
UPDATE food_items SET image_url = 'https://www.themealdb.com/images/media/meals/llcbn01574260722.jpg' WHERE id = 5;
UPDATE food_items SET image_url = 'https://www.themealdb.com/images/media/meals/wvqpwt1468339226.jpg' WHERE id = 6;
UPDATE food_items SET image_url = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQi-ZlEpiXF_m9KKdeH0qhr-CEyc18mIVg5lw&s' WHERE id = 7;
UPDATE food_items SET image_url = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQHSUxI-tULvmJFO_8Otx3-UeeuKJvGV8vMiQ&s' WHERE id = 8;
UPDATE food_items SET image_url = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcToiYRmShJapV_O0JJgU3ygOnOQVgnu7rL10A&s' WHERE id = 9;
UPDATE food_items SET image_url = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRQHDS4k_XMABZYxPrA05U30Q20YXqsf_CqQA&s' WHERE id = 10;

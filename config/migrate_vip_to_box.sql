USE movie_management;

ALTER TABLE seat
    MODIFY category ENUM('VIP','Box','Standard') NOT NULL DEFAULT 'Standard';

UPDATE seat
SET category = 'Box'
WHERE category = 'VIP';

ALTER TABLE seat
    MODIFY category ENUM('Box','Standard') NOT NULL DEFAULT 'Standard';

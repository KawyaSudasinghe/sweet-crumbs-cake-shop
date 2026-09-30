-- Sweet Crumbs administrator account
-- Demo credentials:
-- Email: admin@sweetcrumbs.lk
-- Password: Admin@12345
--
-- Change the password after first login.

INSERT INTO `user`
    (name, email, password_hash, phone, role)
VALUES
    (
        'Sweet Crumbs Admin',
        'admin@sweetcrumbs.lk',
        '$2y$12$w4DileionVXEmIHRedYInuJqHiyKagd9E2URTS8R2Fz0Uvc17xIYu',
        NULL,
        'admin'
    )
ON DUPLICATE KEY UPDATE
    role = 'admin',
    password_hash = VALUES(password_hash),
    name = VALUES(name);

USE swd_blog;

ALTER TABLE admins
    MODIFY name VARCHAR(120) NOT NULL,
    MODIFY email VARCHAR(190) NOT NULL,
    ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE categories
    MODIFY name VARCHAR(120) NOT NULL,
    MODIFY slug VARCHAR(160) NOT NULL,
    ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE posts
    DROP FOREIGN KEY posts_ibfk_1,
    DROP FOREIGN KEY posts_ibfk_2,
    DROP INDEX title,
    MODIFY category_id INT UNSIGNED NOT NULL,
    MODIFY title VARCHAR(240) NOT NULL,
    MODIFY slug VARCHAR(260) NOT NULL,
    MODIFY body LONGTEXT NOT NULL,
    ADD CONSTRAINT fk_posts_category FOREIGN KEY (category_id) REFERENCES categories(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    ADD CONSTRAINT fk_posts_author FOREIGN KEY (author_id) REFERENCES admins(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    ADD FULLTEXT KEY ft_posts_title_body (title, body),
    ADD INDEX idx_posts_status_published (status, published_at);

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211208144241 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Gedmo-related tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE ext_log_entries (
            id INT AUTO_INCREMENT NOT NULL,
            action VARCHAR(8) NOT NULL,
            logged_at DATETIME NOT NULL,
            object_id VARCHAR(64) DEFAULT NULL,
            object_class VARCHAR(191) NOT NULL,
            version INT NOT NULL,
            data LONGTEXT DEFAULT NULL COMMENT '(DC2Type:array)',
            username VARCHAR(191) DEFAULT NULL,
            INDEX log_class_lookup_idx (object_class),
            INDEX log_date_lookup_idx (logged_at),
            INDEX log_user_lookup_idx (username),
            INDEX log_version_lookup_idx (object_id, object_class, version),
            PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC
        SQL
        );

        $this->addSql(<<<'SQL'
            CREATE TABLE ext_translations (
            id INT AUTO_INCREMENT NOT NULL,
            locale VARCHAR(8) NOT NULL,
            object_class VARCHAR(191) NOT NULL,
            field VARCHAR(32) NOT NULL,
            foreign_key VARCHAR(64) NOT NULL,
            content LONGTEXT DEFAULT NULL,
            INDEX translations_lookup_idx ( locale, object_class, foreign_key ),
            UNIQUE INDEX lookup_unique_idx ( locale, object_class, field, foreign_key ),
            PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ext_log_entries');
        $this->addSql('DROP TABLE ext_translations');
    }
}

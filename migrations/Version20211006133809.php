<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211006133809 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE maps (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) NOT NULL,
          name_slug VARCHAR(255) NOT NULL,
          description LONGTEXT DEFAULT NULL,
          image VARCHAR(255) NOT NULL,
          max_zoom SMALLINT DEFAULT 1 NOT NULL,
          start_zoom SMALLINT DEFAULT 1 NOT NULL,
          start_x SMALLINT DEFAULT 1 NOT NULL,
          start_y SMALLINT DEFAULT 1 NOT NULL,
          bounds VARCHAR(255) DEFAULT \'[]\' NOT NULL,
          coordinates_ratio SMALLINT DEFAULT 1 NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_472E08A5DF2B4115 (name_slug),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE maps_factions (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) NOT NULL,
          description LONGTEXT DEFAULT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_354BB9A75E237E06 (name),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE maps_markers (
          id INT AUTO_INCREMENT NOT NULL,
          faction_id INT DEFAULT NULL,
          map_id INT NOT NULL,
          marker_type_id INT NOT NULL,
          is_note_from_user_id INT DEFAULT NULL,
          name VARCHAR(255) NOT NULL,
          description LONGTEXT DEFAULT NULL,
          altitude VARCHAR(255) DEFAULT \'0\' NOT NULL,
          latitude VARCHAR(255) DEFAULT \'0\' NOT NULL,
          longitude VARCHAR(255) DEFAULT \'0\' NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_33F679DD5E237E06 (name),
          INDEX IDX_33F679DD4448F8DA (faction_id),
          INDEX IDX_33F679DD53C55F64 (map_id),
          INDEX IDX_33F679DDBFC01D99 (marker_type_id),
          INDEX IDX_33F679DD148F2654 (is_note_from_user_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE maps_markers_types (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) NOT NULL,
          description LONGTEXT DEFAULT NULL,
          icon VARCHAR(255) NOT NULL,
          icon_width INT NOT NULL,
          icon_height INT NOT NULL,
          icon_center_x INT DEFAULT NULL,
          icon_center_y INT DEFAULT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_C4AFA515E237E06 (name),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE maps_routes (
          id INT AUTO_INCREMENT NOT NULL,
          marker_start_id INT DEFAULT NULL,
          marker_end_id INT DEFAULT NULL,
          map_id INT NOT NULL,
          faction_id INT DEFAULT NULL,
          route_type_id INT NOT NULL,
          is_note_from_user_id INT DEFAULT NULL,
          name VARCHAR(255) DEFAULT NULL,
          description LONGTEXT DEFAULT NULL,
          coordinates LONGTEXT NOT NULL,
          distance DOUBLE PRECISION DEFAULT \'0\' NOT NULL,
          forced_distance DOUBLE PRECISION DEFAULT NULL,
          guarded TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_4A14AA7582929C14 (marker_start_id),
          INDEX IDX_4A14AA75476289B (marker_end_id),
          INDEX IDX_4A14AA7553C55F64 (map_id),
          INDEX IDX_4A14AA754448F8DA (faction_id),
          INDEX IDX_4A14AA753D1FD10B (route_type_id),
          INDEX IDX_4A14AA75148F2654 (is_note_from_user_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE maps_routes_transports (
          id INT AUTO_INCREMENT NOT NULL,
          route_type_id INT NOT NULL,
          transport_type_id INT NOT NULL,
          percentage NUMERIC(9, 6) DEFAULT \'100\' NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_DC8B306C3D1FD10B (route_type_id),
          INDEX IDX_DC8B306C519B4C62 (transport_type_id),
          UNIQUE INDEX unique_route_transport (
            route_type_id, transport_type_id
          ),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE maps_routes_types (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) NOT NULL,
          description LONGTEXT DEFAULT NULL,
          color VARCHAR(75) DEFAULT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_1006B6375E237E06 (name),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE maps_transports_types (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) NOT NULL,
          slug VARCHAR(255) NOT NULL,
          description LONGTEXT DEFAULT NULL,
          speed NUMERIC(8, 4) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_937FC7725E237E06 (name),
          UNIQUE INDEX UNIQ_937FC772989D9B62 (slug),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE maps_zones (
          id INT AUTO_INCREMENT NOT NULL,
          map_id INT NOT NULL,
          faction_id INT DEFAULT NULL,
          zone_type_id INT NOT NULL,
          is_note_from_user_id INT DEFAULT NULL,
          name VARCHAR(255) NOT NULL,
          description LONGTEXT DEFAULT NULL,
          coordinates LONGTEXT NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_436BD5205E237E06 (name),
          INDEX IDX_436BD52053C55F64 (map_id),
          INDEX IDX_436BD5204448F8DA (faction_id),
          INDEX IDX_436BD5207B788FAB (zone_type_id),
          INDEX IDX_436BD520148F2654 (is_note_from_user_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE maps_zones_types (
          id INT AUTO_INCREMENT NOT NULL,
          parent_id INT DEFAULT NULL,
          name VARCHAR(255) NOT NULL,
          description LONGTEXT DEFAULT NULL,
          color VARCHAR(75) DEFAULT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_B4AD3285E237E06 (name),
          INDEX IDX_B4AD328727ACA70 (parent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE users (
          id INT AUTO_INCREMENT NOT NULL,
          username VARCHAR(255) NOT NULL,
          username_canonical VARCHAR(255) NOT NULL,
          email VARCHAR(255) NOT NULL,
          email_canonical VARCHAR(255) NOT NULL,
          password VARCHAR(255) NOT NULL,
          confirmation_token VARCHAR(255) DEFAULT NULL,
          roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\',
          email_confirmed TINYINT(1) DEFAULT \'0\' NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_1483A5E992FC23A8 (username_canonical),
          UNIQUE INDEX UNIQ_1483A5E9A0D96FBF (email_canonical),
          UNIQUE INDEX UNIQ_1483A5E9C05FB297 (confirmation_token),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE maps_markers ADD CONSTRAINT FK_33F679DD4448F8DA FOREIGN KEY (faction_id) REFERENCES maps_factions (id)');
        $this->addSql('ALTER TABLE maps_markers ADD CONSTRAINT FK_33F679DD53C55F64 FOREIGN KEY (map_id) REFERENCES maps (id)');
        $this->addSql('ALTER TABLE maps_markers ADD CONSTRAINT FK_33F679DDBFC01D99 FOREIGN KEY (marker_type_id) REFERENCES maps_markers_types (id)');
        $this->addSql('ALTER TABLE maps_markers ADD CONSTRAINT FK_33F679DD148F2654 FOREIGN KEY (is_note_from_user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE maps_routes ADD CONSTRAINT FK_4A14AA7582929C14 FOREIGN KEY (marker_start_id) REFERENCES maps_markers (id)');
        $this->addSql('ALTER TABLE maps_routes ADD CONSTRAINT FK_4A14AA75476289B FOREIGN KEY (marker_end_id) REFERENCES maps_markers (id)');
        $this->addSql('ALTER TABLE maps_routes ADD CONSTRAINT FK_4A14AA7553C55F64 FOREIGN KEY (map_id) REFERENCES maps (id)');
        $this->addSql('ALTER TABLE maps_routes ADD CONSTRAINT FK_4A14AA754448F8DA FOREIGN KEY (faction_id) REFERENCES maps_factions (id)');
        $this->addSql('ALTER TABLE maps_routes ADD CONSTRAINT FK_4A14AA753D1FD10B FOREIGN KEY (route_type_id) REFERENCES maps_routes_types (id)');
        $this->addSql('ALTER TABLE maps_routes ADD CONSTRAINT FK_4A14AA75148F2654 FOREIGN KEY (is_note_from_user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE maps_routes_transports ADD CONSTRAINT FK_DC8B306C3D1FD10B FOREIGN KEY (route_type_id) REFERENCES maps_routes_types (id)');
        $this->addSql('ALTER TABLE maps_routes_transports ADD CONSTRAINT FK_DC8B306C519B4C62 FOREIGN KEY (transport_type_id) REFERENCES maps_transports_types (id)');
        $this->addSql('ALTER TABLE maps_zones ADD CONSTRAINT FK_436BD52053C55F64 FOREIGN KEY (map_id) REFERENCES maps (id)');
        $this->addSql('ALTER TABLE maps_zones ADD CONSTRAINT FK_436BD5204448F8DA FOREIGN KEY (faction_id) REFERENCES maps_factions (id)');
        $this->addSql('ALTER TABLE maps_zones ADD CONSTRAINT FK_436BD5207B788FAB FOREIGN KEY (zone_type_id) REFERENCES maps_zones_types (id)');
        $this->addSql('ALTER TABLE maps_zones ADD CONSTRAINT FK_436BD520148F2654 FOREIGN KEY (is_note_from_user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE maps_zones_types ADD CONSTRAINT FK_B4AD328727ACA70 FOREIGN KEY (parent_id) REFERENCES maps_zones_types (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE maps_markers DROP FOREIGN KEY FK_33F679DD53C55F64');
        $this->addSql('ALTER TABLE maps_routes DROP FOREIGN KEY FK_4A14AA7553C55F64');
        $this->addSql('ALTER TABLE maps_zones DROP FOREIGN KEY FK_436BD52053C55F64');
        $this->addSql('ALTER TABLE maps_markers DROP FOREIGN KEY FK_33F679DD4448F8DA');
        $this->addSql('ALTER TABLE maps_routes DROP FOREIGN KEY FK_4A14AA754448F8DA');
        $this->addSql('ALTER TABLE maps_zones DROP FOREIGN KEY FK_436BD5204448F8DA');
        $this->addSql('ALTER TABLE maps_routes DROP FOREIGN KEY FK_4A14AA7582929C14');
        $this->addSql('ALTER TABLE maps_routes DROP FOREIGN KEY FK_4A14AA75476289B');
        $this->addSql('ALTER TABLE maps_markers DROP FOREIGN KEY FK_33F679DDBFC01D99');
        $this->addSql('ALTER TABLE maps_routes DROP FOREIGN KEY FK_4A14AA753D1FD10B');
        $this->addSql('ALTER TABLE maps_routes_transports DROP FOREIGN KEY FK_DC8B306C3D1FD10B');
        $this->addSql('ALTER TABLE maps_routes_transports DROP FOREIGN KEY FK_DC8B306C519B4C62');
        $this->addSql('ALTER TABLE maps_zones DROP FOREIGN KEY FK_436BD5207B788FAB');
        $this->addSql('ALTER TABLE maps_zones_types DROP FOREIGN KEY FK_B4AD328727ACA70');
        $this->addSql('ALTER TABLE maps_markers DROP FOREIGN KEY FK_33F679DD148F2654');
        $this->addSql('ALTER TABLE maps_routes DROP FOREIGN KEY FK_4A14AA75148F2654');
        $this->addSql('ALTER TABLE maps_zones DROP FOREIGN KEY FK_436BD520148F2654');
        $this->addSql('DROP TABLE maps');
        $this->addSql('DROP TABLE maps_factions');
        $this->addSql('DROP TABLE maps_markers');
        $this->addSql('DROP TABLE maps_markers_types');
        $this->addSql('DROP TABLE maps_routes');
        $this->addSql('DROP TABLE maps_routes_transports');
        $this->addSql('DROP TABLE maps_routes_types');
        $this->addSql('DROP TABLE maps_transports_types');
        $this->addSql('DROP TABLE maps_zones');
        $this->addSql('DROP TABLE maps_zones_types');
        $this->addSql('DROP TABLE users');
    }
}

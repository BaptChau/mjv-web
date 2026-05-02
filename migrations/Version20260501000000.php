<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Creates the `user` table for public users who sign in via Google OAuth.
 *
 * Columns:
 *   - id          : auto-increment primary key
 *   - email       : unique, used as the Symfony user identifier
 *   - name        : display name pulled from Google profile
 *   - picture     : avatar URL from Google profile (nullable)
 *   - google_id   : Google's stable `sub` claim (nullable so the column can exist before first login)
 *   - roles       : JSON array of Symfony security roles (defaults to ["ROLE_USER"])
 *   - created_at  : immutable timestamp of first sign-in
 */
final class Version20260501000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the public user table for Google OAuth sign-in';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE "user" (
                id         SERIAL       NOT NULL,
                email      VARCHAR(180) NOT NULL,
                name       VARCHAR(255) NOT NULL,
                picture    VARCHAR(255) DEFAULT NULL,
                google_id  VARCHAR(255) DEFAULT NULL,
                roles      JSON         NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);

        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64976F5C865 ON "user" (google_id)');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE "user"');
    }
}

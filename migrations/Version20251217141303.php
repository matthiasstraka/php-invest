<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251217141303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE account (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, number VARCHAR(255) DEFAULT NULL, iban VARCHAR(255) DEFAULT NULL, type SMALLINT UNSIGNED DEFAULT 1 NOT NULL, currency CHAR(3) NOT NULL --ISO 4217 Code
        , timezone VARCHAR(255) NOT NULL, star BOOLEAN DEFAULT 0 NOT NULL --User\'s favorite
        , owner_id INTEGER NOT NULL, CONSTRAINT FK_7D3656A47E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_7D3656A47E3C61F9 ON account (owner_id)');
        $this->addSql('CREATE TABLE account_transaction (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, transaction_id BIGINT UNSIGNED DEFAULT NULL --Unique broker transaction ID
        , time DATETIME NOT NULL, portfolio NUMERIC(10, 4) DEFAULT NULL, cash NUMERIC(10, 4) DEFAULT NULL, commission NUMERIC(10, 4) DEFAULT NULL, tax NUMERIC(10, 4) DEFAULT NULL, interest NUMERIC(10, 4) DEFAULT NULL, consolidation NUMERIC(10, 4) DEFAULT NULL, notes CLOB DEFAULT NULL, consolidated BOOLEAN DEFAULT 0 NOT NULL, account_id INTEGER NOT NULL, CONSTRAINT FK_A370F9D29B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_A370F9D29B6B5FBA ON account_transaction (account_id)');
        $this->addSql('CREATE UNIQUE INDEX UNQ_account_transaction_id ON account_transaction (account_id, transaction_id)');
        $this->addSql('CREATE TABLE asset (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, isin CHAR(12) NOT NULL, name VARCHAR(255) NOT NULL, symbol VARCHAR(255) NOT NULL, type SMALLINT UNSIGNED NOT NULL, currency CHAR(3) NOT NULL --ISO 4217 Code
        , country CHAR(2) DEFAULT NULL --ISO 3166-1 Alpha-2 code
        , url VARCHAR(2048) DEFAULT NULL, irurl VARCHAR(2048) DEFAULT NULL, newsurl VARCHAR(2048) DEFAULT NULL, notes CLOB DEFAULT NULL, pricedatasource VARCHAR(255) DEFAULT NULL --Datasource expression to download price data
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2AF5A5C2FE82D2D ON asset (isin)');
        $this->addSql('CREATE TABLE asset_note (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, date DATE NOT NULL, type SMALLINT UNSIGNED NOT NULL, text CLOB DEFAULT NULL, url VARCHAR(2048) DEFAULT NULL, asset_id INTEGER DEFAULT NULL, author_id INTEGER DEFAULT NULL, CONSTRAINT FK_2BD93FDC5DA1941 FOREIGN KEY (asset_id) REFERENCES asset (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2BD93FDCF675F31B FOREIGN KEY (author_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_2BD93FDC5DA1941 ON asset_note (asset_id)');
        $this->addSql('CREATE INDEX IDX_2BD93FDCF675F31B ON asset_note (author_id)');
        $this->addSql('CREATE TABLE asset_price (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date DATE NOT NULL, open NUMERIC(10, 4) NOT NULL, high NUMERIC(10, 4) NOT NULL, low NUMERIC(10, 4) NOT NULL, close NUMERIC(10, 4) NOT NULL, volume INTEGER UNSIGNED NOT NULL, asset_id INTEGER NOT NULL, CONSTRAINT FK_5F930C9E5DA1941 FOREIGN KEY (asset_id) REFERENCES asset (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_5F930C9E5DA1941 ON asset_price (asset_id)');
        $this->addSql('CREATE UNIQUE INDEX UNQ_asset_date ON asset_price (asset_id, date)');
        $this->addSql('CREATE TABLE country (code CHAR(2) NOT NULL --ISO 3166-1 Alpha-2 code
        , PRIMARY KEY (code))');
        $this->addSql('CREATE TABLE currency (code CHAR(3) NOT NULL --ISO 4217 Code
        , isin_usd CHAR(12) DEFAULT NULL, PRIMARY KEY (code))');
        $this->addSql('CREATE TABLE execution (execution_id BIGINT UNSIGNED DEFAULT NULL --Unique broker execution ID
        , volume NUMERIC(12, 6) NOT NULL, price NUMERIC(10, 4) NOT NULL, currency CHAR(3) NOT NULL --ISO 4217 Code
        , exchange_rate NUMERIC(10, 4) DEFAULT \'1\' NOT NULL, direction SMALLINT DEFAULT 1 NOT NULL, type SMALLINT UNSIGNED DEFAULT 1 NOT NULL, marketplace VARCHAR(255) DEFAULT NULL, transaction_id INTEGER NOT NULL, instrument_id INTEGER NOT NULL, PRIMARY KEY (transaction_id), CONSTRAINT FK_2A0D73A2FC0CB0F FOREIGN KEY (transaction_id) REFERENCES account_transaction (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2A0D73ACF11D9C FOREIGN KEY (instrument_id) REFERENCES instrument (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_2A0D73ACF11D9C ON execution (instrument_id)');
        $this->addSql('CREATE TABLE instrument (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, isin CHAR(12) DEFAULT NULL, name VARCHAR(255) NOT NULL, emission_date DATE DEFAULT NULL, termination_date DATE DEFAULT NULL, eusipa SMALLINT UNSIGNED DEFAULT 0 NOT NULL --EUSIPA / extended class code
        , direction SMALLINT DEFAULT 1 NOT NULL, status SMALLINT UNSIGNED DEFAULT 0 NOT NULL, currency CHAR(3) NOT NULL --ISO 4217 Code
        , issuer VARCHAR(255) DEFAULT NULL, url VARCHAR(2048) DEFAULT NULL, notes CLOB DEFAULT NULL, execution_tax_rate NUMERIC(5, 4) DEFAULT NULL, underlying_id INTEGER NOT NULL, CONSTRAINT FK_3CBF69DDA8E693F4 FOREIGN KEY (underlying_id) REFERENCES asset (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3CBF69DD2FE82D2D ON instrument (isin)');
        $this->addSql('CREATE INDEX IDX_3CBF69DDA8E693F4 ON instrument (underlying_id)');
        $this->addSql('CREATE TABLE instrument_terms (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date DATE NOT NULL, ratio NUMERIC(10, 4) DEFAULT NULL --Ratio in percent
        , cap NUMERIC(10, 4) DEFAULT NULL, strike NUMERIC(10, 4) DEFAULT NULL, bonus_level NUMERIC(10, 4) DEFAULT NULL, reverse_level NUMERIC(10, 4) DEFAULT NULL, barrier NUMERIC(10, 4) DEFAULT NULL, interest_rate NUMERIC(5, 4) DEFAULT NULL, margin NUMERIC(5, 4) DEFAULT NULL --Margin requirement in percent
        , instrument_id INTEGER NOT NULL, CONSTRAINT FK_50788B18CF11D9C FOREIGN KEY (instrument_id) REFERENCES instrument (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_50788B18CF11D9C ON instrument_terms (instrument_id)');
        $this->addSql('CREATE UNIQUE INDEX UNQ_terms_instrument_date ON instrument_terms (instrument_id, date)');
        $this->addSql('CREATE TABLE transaction_attachment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, mimetype VARCHAR(255) DEFAULT NULL, content BLOB NOT NULL, time_uploaded DATETIME NOT NULL, transaction_id INTEGER NOT NULL, CONSTRAINT FK_96C9EE0A2FC0CB0F FOREIGN KEY (transaction_id) REFERENCES account_transaction (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_96C9EE0A2FC0CB0F ON transaction_attachment (transaction_id)');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, roles CLOB NOT NULL, currency CHAR(3) DEFAULT \'USD\' NOT NULL --ISO 4217 Code
        , password VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON user (username)');
        $this->addSql('CREATE TABLE rememberme_token (series VARCHAR(88) NOT NULL, value VARCHAR(88) NOT NULL, lastUsed DATETIME NOT NULL, class VARCHAR(100) DEFAULT \'\' NOT NULL, username VARCHAR(200) NOT NULL, PRIMARY KEY (series))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE account');
        $this->addSql('DROP TABLE account_transaction');
        $this->addSql('DROP TABLE asset');
        $this->addSql('DROP TABLE asset_note');
        $this->addSql('DROP TABLE asset_price');
        $this->addSql('DROP TABLE country');
        $this->addSql('DROP TABLE currency');
        $this->addSql('DROP TABLE execution');
        $this->addSql('DROP TABLE instrument');
        $this->addSql('DROP TABLE instrument_terms');
        $this->addSql('DROP TABLE transaction_attachment');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE rememberme_token');
    }
}

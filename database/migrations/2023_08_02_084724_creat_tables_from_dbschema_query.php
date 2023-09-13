<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
CREATE  TABLE masonic_orders (
	id                   INT UNSIGNED NOT NULL AUTO_INCREMENT  PRIMARY KEY,
	created_by           INT       ,
	created_at         DATE   DEFAULT (CURRENT_DATE)    ,
	state                TINYINT   DEFAULT (1)    ,
	ordering             INT   DEFAULT (0)    ,
	name           VARCHAR(100)       ,
	short_name            VARCHAR(100)
 ) engine=InnoDB;
");
        DB::statement("

        CREATE  TABLE organisations (
        id                   INT UNSIGNED NOT NULL  AUTO_INCREMENT   PRIMARY KEY,
	masonic_order_id     INT(10) UNSIGNED       ,
	created_by           INT       ,
	created_at         DATETIME   DEFAULT (CURRENT_TIMESTAMP)    ,
	state                TINYINT   DEFAULT (1)    ,
	name              VARCHAR(100)
 ) engine=InnoDB;
");
        DB::statement("

CREATE  TABLE region_types (
        id                   INT UNSIGNED NOT NULL  AUTO_INCREMENT   PRIMARY KEY,
	created_by           INT       ,
	created_at         DATETIME   DEFAULT (CURRENT_TIMESTAMP)    ,
	ordering             INT   DEFAULT (0)    ,
	state                TINYINT   DEFAULT (1)    ,
	name     VARCHAR(100)       ,
	shortcode VARCHAR(10)
 ) engine=InnoDB;
");
        DB::statement("

CREATE  TABLE regions (
        id                   INT UNSIGNED NOT NULL  AUTO_INCREMENT   PRIMARY KEY,
	org_id               INT(10) UNSIGNED       ,
	created_by           INT       ,
	created_at         DATETIME   DEFAULT (CURRENT_TIMESTAMP)    ,
	region_type_id       INT(10) UNSIGNED       ,
	name           VARCHAR(100)       ,
	short_name            VARCHAR(100)       ,
	long_name             VARCHAR(100)
 ) engine=InnoDB;
");
        DB::statement("
CREATE  TABLE titles (
        id                   INT UNSIGNED NOT NULL  AUTO_INCREMENT   PRIMARY KEY,
	created_by           INT       ,
	created_at         DATETIME   DEFAULT (CURRENT_TIMESTAMP)    ,
	state                TINYINT   DEFAULT (1)    ,
	ordering             INT   DEFAULT (0)    ,
	title                INT
 ) engine=InnoDB;
");
        DB::statement("
CREATE  TABLE unit_types (
        id                   INT UNSIGNED NOT NULL  AUTO_INCREMENT   PRIMARY KEY,
	created_by           INT       ,
	created_at         DATETIME   DEFAULT (CURRENT_TIMESTAMP)    ,
	ordering             INT   DEFAULT (0)    ,
	state                TINYINT   DEFAULT (1)    ,
	name       VARCHAR(100)       ,
	shortcode  VARCHAR(10)
 ) engine=InnoDB;
");
        DB::statement("
CREATE  TABLE units (
        id                   INT UNSIGNED NOT NULL  AUTO_INCREMENT   PRIMARY KEY,
	region_id            INT(10) UNSIGNED       ,
	created_by           INT       ,
	created_at         DATETIME   DEFAULT (CURRENT_TIMESTAMP)    ,
	state                TINYINT   DEFAULT (1)    ,
	unit_type_id         INT(10) UNSIGNED   DEFAULT (0)    ,
	name             VARCHAR(100) NOT NULL      ,
	unit_no               INT NOT NULL
 ) engine=InnoDB;
");
        DB::statement("
CREATE  TABLE masonic_degrees (
        id                   INT UNSIGNED NOT NULL  AUTO_INCREMENT   PRIMARY KEY,
	created_by           INT       ,
	created_at         DATETIME   DEFAULT (CURRENT_TIMESTAMP)    ,
	ordering             INT   DEFAULT (0)    ,
	state                TINYINT   DEFAULT (1)    ,
	masonic_order_id     INT(10) UNSIGNED       ,
	name          VARCHAR(100)
 ) engine=InnoDB;
");
        DB::statement("
CREATE  TABLE people (
        id                   INT UNSIGNED NOT NULL   AUTO_INCREMENT  PRIMARY KEY,
	created_by           INT       ,
	created_at         DATETIME   DEFAULT (CURRENT_TIMESTAMP)    ,
	state                TINYINT   DEFAULT (1)    ,
	title_id             INT(10) UNSIGNED       ,
	first_name            VARCHAR(100)       ,
	last_name             VARCHAR(100)       ,
	preferred_name        VARCHAR(100)       ,
	familiar_name         VARCHAR(100)
 ) engine=InnoDB;
");
        DB::statement("
CREATE  TABLE members (
        id                   INT UNSIGNED NOT NULL   AUTO_INCREMENT  PRIMARY KEY,
	created_by           INT       ,
	created_at         DATETIME   DEFAULT (CURRENT_TIMESTAMP)    ,
	ordering             INT   DEFAULT (0)    ,
	state                TINYINT   DEFAULT (1)    ,
	org_id      INT(10) UNSIGNED       ,
	people_id            INT(10) UNSIGNED       ,
	adelphi_id           INT(10) UNSIGNED       ,
	glref                VARCHAR(10)
 ) engine=InnoDB;
");
        DB::statement("
CREATE  TABLE memberships (
        id                   INT UNSIGNED NOT NULL  AUTO_INCREMENT   PRIMARY KEY,
	created_by           INT       ,
	created_at         DATETIME   DEFAULT (CURRENT_TIMESTAMP)    ,
	ordering             INT   DEFAULT (0)    ,
	state                TINYINT   DEFAULT (1)    ,
	member_id           INT(10) UNSIGNED NOT NULL      ,
	unit_id              INT(10) UNSIGNED NOT NULL      ,
	in_out               TINYINT DEFAULT (1) NOT NULL   ,
	date_effective       DATE NOT NULL      ,
	date_reported        DATE       ,
	billing_start_date   DATE       ,
	billing_end_date     DATE       ,
	parent_id    INT(10) UNSIGNED
 ) engine=InnoDB;
");
        DB::statement("
ALTER TABLE masonic_degrees ADD CONSTRAINT fk_masonic_degrees FOREIGN KEY ( masonic_order_id ) REFERENCES masonic_orders( id ) ON DELETE NO ACTION ON UPDATE NO ACTION;
");
        DB::statement("
ALTER TABLE members ADD CONSTRAINT fk_members_masonic_orders FOREIGN KEY ( org_id ) REFERENCES organisations( id ) ON DELETE NO ACTION ON UPDATE NO ACTION;
");
        DB::statement("
ALTER TABLE members ADD CONSTRAINT fk_members_people FOREIGN KEY ( people_id ) REFERENCES people( id ) ON DELETE NO ACTION ON UPDATE NO ACTION;
");
        DB::statement("
ALTER TABLE memberships ADD CONSTRAINT fk_memberships_members FOREIGN KEY ( member_id ) REFERENCES members( id ) ON DELETE NO ACTION ON UPDATE NO ACTION;
");
        DB::statement("
ALTER TABLE memberships ADD CONSTRAINT fk_memberships_units FOREIGN KEY ( unit_id ) REFERENCES units( id ) ON DELETE NO ACTION ON UPDATE NO ACTION;
");
        DB::statement("
ALTER TABLE organisations ADD CONSTRAINT fk_organisations FOREIGN KEY ( masonic_order_id ) REFERENCES masonic_orders( id ) ON DELETE NO ACTION ON UPDATE NO ACTION;
");
        DB::statement("
ALTER TABLE people ADD CONSTRAINT fk_people_titles FOREIGN KEY ( title_id ) REFERENCES titles( id ) ON DELETE NO ACTION ON UPDATE NO ACTION;
");
        DB::statement("
ALTER TABLE regions ADD CONSTRAINT fk_regions_organisations FOREIGN KEY ( org_id ) REFERENCES organisations( id ) ON DELETE NO ACTION ON UPDATE NO ACTION;
");
        DB::statement("
ALTER TABLE regions ADD CONSTRAINT fk_regions_region_type FOREIGN KEY ( region_type_id ) REFERENCES region_types( id ) ON DELETE NO ACTION ON UPDATE NO ACTION;
");
        DB::statement("
ALTER TABLE units ADD CONSTRAINT fk_units_regions FOREIGN KEY ( region_id ) REFERENCES regions( id ) ON DELETE NO ACTION ON UPDATE NO ACTION;
");
        DB::statement("
ALTER TABLE units ADD CONSTRAINT fk_units_unit_types FOREIGN KEY ( unit_type_id ) REFERENCES unit_types( id ) ON DELETE NO ACTION ON UPDATE NO ACTION;
");

        DB::statement("
ALTER TABLE memberships ADD CONSTRAINT fk_memberships_parent FOREIGN KEY ( parent_id ) REFERENCES memberships( id ) ON DELETE NO ACTION ON UPDATE NO ACTION;
");
        DB::statement("
CREATE INDEX memberships_in_out_index ON memberships (in_out);
");
        DB::statement("
CREATE INDEX memberships_date_effective_index ON memberships (date_effective);
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memberships');
        Schema::dropIfExists('members');
        Schema::dropIfExists('masonic_degrees');
        Schema::dropIfExists('units');
        Schema::dropIfExists('regions');
        Schema::dropIfExists('organisations');
        Schema::dropIfExists('masonic_orders');
        Schema::dropIfExists('region_types');
        Schema::dropIfExists('people');
        Schema::dropIfExists('unit_types');
        Schema::dropIfExists('titles');
    }
};

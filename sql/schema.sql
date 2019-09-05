DROP DATABASE IF EXISTS download_msm89xx;
CREATE DATABASE download_msm89xx;
USE download_msm89xx;

CREATE TABLE board (
    board_id int NOT NULL AUTO_INCREMENT,
    board_name varchar(15) NOT NULL,
    board_arch ENUM('arm', 'arm64') NOT NULL DEFAULT 'arm',
    PRIMARY KEY (board_id),
    INDEX USING BTREE (board_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE device (
    device_id int NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    release_date DATE DEFAULT NULL,
    PRIMARY KEY (device_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE variant (
    variant_id int NOT NULL AUTO_INCREMENT,
    board_id int NOT NULL DEFAULT 1,
    device_id int NOT NULL,
    codename VARCHAR(20) NOT NULL,
    model varchar(15),
    codename_model_extra JSON DEFAULT NULL,
    unified BOOLEAN NOT NULL DEFAULT false,
    PRIMARY KEY (variant_id),
    INDEX USING BTREE (codename),
    CONSTRAINT FOREIGN KEY (board_id)
        REFERENCES board(board_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT FOREIGN KEY (device_id)
        REFERENCES device(device_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8; 

CREATE TABLE tag_format (
    format_id int NOT NULL AUTO_INCREMENT,
    date_offset int DEFAULT -1,
    dist_offset int DEFAULT -1,
    device_offset int DEFAULT -1,
    build_offset int DEFAULT -1,
    version_offset int DEFAULT -1,
    replace_uscore BOOLEAN DEFAULT TRUE,
    channel_offset int DEFAULT -1,
    extra_offset int DEFAULT -1,
    PRIMARY KEY (format_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- name_short is mostly redundant - required for old urls in GET requests
CREATE TABLE dist (
    dist_id int NOT NULL AUTO_INCREMENT,
    format_id int NOT NULL DEFAULT 1,
    tag_prefix VARCHAR(15) NOT NULL,
    name_short VARCHAR(30) NOT NULL,
    name_long VARCHAR(50) NOT NULL,
    PRIMARY KEY (dist_id),
    CONSTRAINT FOREIGN KEY (format_id)
        REFERENCES tag_format(format_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- could not create a table with name 'release', opted for 'build' instead --
CREATE TABLE build (
    build_id int NOT NULL AUTO_INCREMENT,
    build_tag VARCHAR(100) NOT NULL,
    build_date DATE DEFAULT NULL,
    build_version VARCHAR(15),
    build_channel VARCHAR(15) DEFAULT NULL,
    build_num int DEFAULT 0,
    last_update TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    variant_id int DEFAULT 1,
    dist_id int NOT NULL,
    PRIMARY KEY (build_id),
    INDEX USING BTREE (build_tag),
    INDEX USING BTREE (build_date),
    INDEX USING BTREE (build_version),
    CONSTRAINT FOREIGN KEY (variant_id)
        REFERENCES variant(variant_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT FOREIGN KEY (dist_id)
        REFERENCES dist(dist_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE artifact_type (
    type_id int NOT NULL AUTO_INCREMENT,
    extension VARCHAR(10) NOT NULL,
    description VARCHAR(50) NOT NULL,
    PRIMARY KEY (type_id),
    INDEX USING BTREE (extension)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE artifact (
    artifact_id int NOT NULL AUTO_INCREMENT,
    file_size int NOT NULL DEFAULT 0,
    build_id int NOT NULL,
    type_id int DEFAULT 1,
    download_count int DEFAULT 0,
    file_name VARCHAR(100),
    download_url VARCHAR(512),
    PRIMARY KEY (artifact_id),
    INDEX USING BTREE (download_count),
    UNIQUE INDEX idx_file USING BTREE (file_name, file_size),
    CONSTRAINT FOREIGN KEY (build_id)
        REFERENCES build(build_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT FOREIGN KEY (type_id)
        REFERENCES artifact_type(type_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- create views for joined tables --
CREATE VIEW devices AS
    SELECT
        device.device_id as device_id,
        device.name as name,
        device.release_date as release_date,
        board.board_name as board_name,
        board.board_arch as board_arch,
        variant.variant_id as variant_id,
        variant.codename as codename,
        variant.model as model,
        variant.unified as unified,
        variant.codename_model_extra as codename_model_extra
    FROM device
    JOIN variant ON device.device_id = variant.device_id
    JOIN board ON board.board_id = variant.board_id
;

CREATE VIEW dists AS
    SELECT
        dist.dist_id as dist_id,
        dist.tag_prefix as tag_prefix,
        dist.name_short as name_short,
        dist.name_long as name_long,
        tag_format.date_offset as date_offset,
        tag_format.dist_offset as dist_offset,
        tag_format.device_offset as device_offset,
        tag_format.build_offset as build_offset,
        tag_format.version_offset as version_offset,
        tag_format.channel_offset as channel_offset,
        tag_format.extra_offset as extra_offset,
        tag_format.replace_uscore as replace_uscore
    FROM dist
    JOIN tag_format ON dist.format_id = tag_format.format_id
;

CREATE VIEW artifacts AS
    SELECT
        artifact.build_id as build_id,
        artifact.artifact_id as artifact_id,
        artifact.file_name as file_name,
        artifact.file_size as file_size,
        artifact.download_count as download_count,
        artifact.download_url as download_url,
        artifact_type.description as description
    FROM artifact
    JOIN artifact_type ON artifact.type_id = artifact_type.type_id
;

CREATE VIEW dist_device_builds AS
    SELECT
        build.build_id as build_id,
        build.dist_id as dist_id,
        build.build_tag as build_tag,
        build.build_date as build_date,
        build.build_version as build_version,
        build.build_channel as build_channel,
        build.build_num as build_num,
        build.last_update as last_update,
        build.variant_id as variant_id,
        dist.tag_prefix as tag_prefix,
        dist.name_short as dist_name_short,
        dist.name_long as dist_name_long,
        devices.name as device_name,
        devices.board_name as board_name,
        devices.board_arch as board_arch,
        devices.codename as codename,
        devices.model as model,
        devices.unified as unified,
        devices.codename_model_extra as codename_model_extra
    FROM build
    JOIN devices ON build.variant_id=devices.variant_id
    JOIN dist ON build.dist_id=dist.dist_id
;
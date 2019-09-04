USE download_msm89xx;

INSERT INTO board (board_id, board_name, board_arch) values
    (1, "msm8916", "arm"),
    (2, "msm8953", "arm64")
;

-- dummy device for unrecognised or non-ROM/Boot/Recovery images
INSERT INTO device (device_id, name) values
    (1, "Samsung Galaxy device")
;

-- dummy variant for unrecognised or non-ROM/Boot/Recovery images
INSERT INTO variant (variant_id, device_id, codename, model, board_id) values
    (1, 1, "generic_msm8916", "generic", 1),
    (2, 1, "generic_msm8953", "generic", 2)
;

-- The rest of the devices
INSERT INTO device (name, release_date) values
    ("Samsung Galaxy GRAND Prime", '2015-06-01'),
    ("Samsung Galaxy Core Prime", '2015-06-02'),
    ("Samsung Galaxy Tab E", '2015-06-01'),
    ("Samsung Galaxy Tab A", '2015-05-01'),
    ("Samsung Galaxy A3", '2014-10-01'),
    ("Samsung Galaxy A5", '2014-10-01'),
    ("Samsung Galaxy J5", '2015-06-01'),
    ("Samsung Galaxy J7", '2015-06-01'),
    ("Samsung Galaxy On7", '2015-10-01'),
    ("Samsung Galaxy S4 Mini VE", '2015-08-03')
;

INSERT INTO variant (device_id, codename, model, unified, codename_model_extra) values
    -- fortuna devices --
    (2, "fortuna3g", "SM-G530H", false, NULL),
    (2, "fortunave3g", "SM-G530H", false, NULL),
    (2, "fortunalteub", "SM-G530M", false, "{'fortunalte': ''}"),
    (2, "gprimeltectc", "SM-G5309W", false, "{'fortunaltectc': ''}"),
    (2, "gprimeltezt", "SM-G530MU", false, "{'fortunaltezt': ''}"),
    (2, "gprimeltexx", "SM-G530FZ", false, "{'fortunaltexx': ''}"),
    (2, "gprimelte", "SM-G530W", true, "{'gprimeltetmo': 'SM-G530T', 'gprimeltemtr': 'SM-G530T1', 'gprimeltecan': 'SM-G530W'}"),
    (2, "gprimeltetfnvzw", "SM-S920L", false, NULL),
    (2, "gprimeltespr", "SM-G530P", false, NULL),
    -- core prime --
    (3, "coreprimeltespr", "SM-G360P", false, NULL),
    -- gt devices --
    (4, "gtesqltespr", "SM-T377P", false, NULL),
    (4, "gtelwifiue", "SM-T560NU", false, NULL),
    (5, "gt510wifi", "SM-T550", false, NULL),
    (5, "gt58wifi", "SM-T350", false, NULL),
    (5, "gt58ltetmo", "SM-T357T", false, NULL),
    -- a3 devices --
    (6, "a33g", "SM-A300H", false, NULL),
    (6, "a3lte", "SM-A300F", true, "{'a3ltexx': 'SM-A300F', 'a3ltezt': 'SM-A300YZ', 'a3ltechn': 'SM-A3000', 'a3ltectc': 'SM-A3009', 'a3lteub': 'SM-A300M', 'a3ltezso': 'SM-A300G'}"),
    (6, "a3ulte", "A300FU", true, "{'a3ultexx': 'SM-A300FU', 'a3ultedv': 'SM-A300Y'}"),
    -- a5 devices --
    (7, "a5ltechn", "SM-A5000", false, NULL),
    (7, "a5ltectc", "SM-A5009", false, NULL),
    -- j5 devices --
    (8, "j53gxx", "SM-J500H", false, NULL),
    (8, "j5ltechn", "SM-J5008", false, NULL),
    (8, "j5nlte", "SM-J500FN", false, "{'j5nltexx': ''}"),
    (8, "j5xnlte", "SM-J510FN", true, "{'j5xnltexx': 'SM-J510FN', 'j5xnltejv': 'SM-J510F', 'j5xnltedx': 'SM-J510GN'}"),
    (8, "j5lte", "SM-J500F", true, "{'j5ltexx': 'SM-J500F', 'j5ylte': 'SM-J500Y', 'j5lteub': 'SM-J500M', 'j5ltedx': 'SM-J500G'}"),
    -- j7 devices -- 
    (9, "j7ltespr", "SM-J700P", false, NULL),
    (9, "j7ltechn", "SM-J7008", false, NULL),
    -- on7 devices -- 
    (10, "o7prolte", "SM-G600FY", false, "{'o7proltedd': ''}"),
    -- (9, "o7prolte", "SM-G600FY", true, "{'o7proltedd', 'on7ltechn'}", "{'SM-G600FY', 'SM-G6000'}"),
    -- serrano devices -- 
    (11, "serranovelte", "SM-I9195I", false, NULL),
    (11, "serranove3g", "SM-I9192I", false, NULL)
;

INSERT INTO tag_format (date_offset, dist_offset, device_offset, build_offset, version_offset, replace_uscore, channel_offset) values
    (-1, 0, -1, -1, -1, false, -1),
    (5, 0, 6, 4, 1, true, -1),
    (3, 0, 5, 2, 1, true, 4),
    (5, 0, 6, 4, 3, false, -1),
    (4, 0, 6, 3, 2, true, 5)
;

INSERT INTO dist (format_id, tag_prefix, name_short, name_long) values
    (1, "ZRAM", "ZRAM", "ZRAM Toggle"),
    (1, "MindTheGapps", "MindTheGapps", "MindTheGapps"),
    (1, "OpenGApps", "OpenGApps", "OpenGApps"),
    (2, "TWRP", "TWRP", "TeamWin Recovery Project"),
    (3, "rr", "ResurrectionRemix", "Resurrection Remix"),
    (3, "dot", "DotOS", "DotOS"),
    (3, "lineage-1", "LineageOS", "LineageOS"),
    (4, "oc_hotplug", "Kernels", "Overclock + Hotplug Kernel"),
    (5, "lineage-go", "LineageOS_Go", "LineageOS Go")
;

INSERT INTO artifact_type (extension, description) values
    ("", "N/A"),
    ("txt", "Changelog"),
    ("tar", "ODIN-Flashable image"),
    ("img", "Flashable partition image"),
    ("zip", "Recovery Flashable (ROM) image"),
    ("md5", "MD5 Checksum"),
    ("prop", "System Prop")
;

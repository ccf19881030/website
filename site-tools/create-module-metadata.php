<?php

require_once(__DIR__ . '/../common/code/boost_libraries.php');

function main() {
    $args = $_SERVER['argv'];
    $boost_root = null;

    switch (count($args)) {
        case 2: $boost_root = $args[1]; break;
        default:
            echo "Usage: update-doc-list.php boost_root";
            exit(1);
    }

    $libs = boost_libraries::from_xml_file(__DIR__ . '/../doc/libraries.xml');

    $library_details = $libs->get_for_version(BoostVersion::develop());

    $libraries_by_module = array();

    foreach ($library_details as $library) {
        $module = $library['module'];

        if (isset($library['documentation'])) {
            $doc_url = $library['documentation'];
            $module_base = "libs/$module";

            if ($doc_url == $module_base) {
                $doc_url = '';
            }
            else if (strpos($doc_url, "$module_base/") === 0) {
                $doc_url = substr($doc_url, strlen("$module_base/"));
            }
            else {
                $doc_url = "/$doc_url";
            }

            if (!$doc_url) {
                unset($library['documentation']);
            }
            else {
                $library['documentation'] = $doc_url;
            }
        }

        $libraries_by_module[$module][] = $library;
    }

    foreach ($libraries_by_module as $module => $libraries) {
        $module_libraries = boost_libraries::from_array($libraries);
        $module_dir = "$boost_root/libs/$module";
        $meta_dir = "$module_dir/meta";
        $meta_file = "$module_dir/meta/libraries.json";

        if (!is_dir($module_dir)) {
            echo "Module doesn't exist: $module\n";
            continue;
        }

        if (!is_dir($meta_dir)) {
            mkdir($meta_dir);
        }

        file_put_contents($meta_file, $module_libraries->to_json(
                array('update-version', 'module')));
    }
}


main();
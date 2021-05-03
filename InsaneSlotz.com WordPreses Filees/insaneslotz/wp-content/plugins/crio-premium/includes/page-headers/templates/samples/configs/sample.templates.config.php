<?php
return array(
	array(
		'template_name'    => 'Sample Layout 1',
		'menus'            => array(
			array(
				'type'  => 'social_menu',
				'align' => 'right',
			),
			array(
				'type'  => 'main_menu',
				'align' => 'right',
			),
		),
		'postarr'          => array(
			'post_title'  => 'Sample Layout 1',
			'post_status' => 'publish',
			'post_type'   => 'crio_page_header',
		),
		'template_content' => include __DIR__ . '/sample-layout-1.content.php',
	),
	array(
		'template_name'    => 'Sample Layout 2',
		'menus'            => array(
			array(
				'type'  => 'main_menu',
				'align' => 'center',
			),
		),
		'postarr'          => array(
			'post_title'  => 'Sample Layout 2',
			'post_status' => 'publish',
			'post_type'   => 'crio_page_header',
		),
		'template_content' => include __DIR__ . '/sample-layout-2.content.php',
	),
	array(
		'template_name'    => 'Sample Layout 3',
		'menus'            => array(
			array(
				'type'  => 'main_menu',
				'align' => 'right',
			),
		),
		'postarr'          => array(
			'post_title'  => 'Sample Layout 3',
			'post_status' => 'publish',
			'post_type'   => 'crio_page_header',
		),
		'template_content' => include __DIR__ . '/sample-layout-3.content.php',
	),
	array(
		'template_name'    => 'Sample Layout 4',
		'menus'            => array(
			array(
				'type'  => 'main_menu',
				'align' => 'center',
			),
		),
		'postarr'          => array(
			'post_title'  => 'Sample Layout 4',
			'post_status' => 'publish',
			'post_type'   => 'crio_page_header',
		),
		'template_content' => include __DIR__ . '/sample-layout-4.content.php',
	),
	array(
		'template_name'    => 'Sample Layout 5',
		'menus'            => array(
			array(
				'type'  => 'main_menu',
				'align' => 'left',
			),
		),
		'postarr'          => array(
			'post_title'  => 'Sample Layout 5',
			'post_status' => 'publish',
			'post_type'   => 'crio_page_header',
		),
		'template_content' => include __DIR__ . '/sample-layout-5.content.php',
	),
	array(
		'template_name'    => 'Sample Layout 6',
		'menus'            => array(
			array(
				'type'  => 'main_menu',
				'align' => 'right',
			),
		),
		'postarr'          => array(
			'post_title'  => 'Sample Layout 6',
			'post_status' => 'publish',
			'post_type'   => 'crio_page_header',
		),
		'template_content' => include __DIR__ . '/sample-layout-6.content.php',
	),
);

<?php
$content = <<<'EOD'
<div class="boldgrid-section"
style="background-image: url('https://source.unsplash.com/RCAhiGJsUUE/1920x1080'); background-size: cover; background-position: 50% 50%;"
data-image-url="https://source.unsplash.com/RCAhiGJsUUE/1920x1080">
	<div class="container">
		<div class="row" style="padding-bottom: 30px;">
			<div class="col-md-3 col-sm-3 col-xs-12">
				<div class="boldgrid-component-logo boldgrid-shortcode" data-imhwpb-draggable="true"
					style="margin: 15px 0px;">

					[boldgrid_component type="wp_boldgrid_component_logo" opts="%7B%22widget-boldgrid_component_logo%5B%5D%5Bbgc_logo_alignment%5D%22%3A%22left%22%7D"]

				</div>
			</div>
			<div class="col-md-9 col-xs-12 col-sm-9">
				<div class="boldgrid-component-menu boldgrid-shortcode standard-menu-enabled header-top"
					data-imhwpb-draggable="true" style="margin-top: 5px;">

					{{menu-0}}

				</div>
				<div class="boldgrid-component-menu boldgrid-shortcode standard-menu-enabled header-top"
					data-imhwpb-draggable="true">

					{{menu-1}}

				</div>
			</div>
		</div>
		<div class="row" style="padding-bottom: 120px; padding-top: 60px;">
			<div class="col-md-12 col-sm-12 col-xs-12">
				<div class="boldgrid-shortcode bgc-heading bgc-page-title" data-imhwpb-draggable="true"
					style="font-size: 42px; margin: 15px 0px 0px; color: #ffffff;">

					[boldgrid_component type="wp_boldgrid_component_page_title" opts="%7B%22widget-boldgrid_component_page_title%5B%5D%5Bbgc_title_alignment%5D%22%3A%22center%22%2C%22widget-boldgrid_component_page_title%5B%5D%5Bbgc_heading_type%5D%22%3A%22h1%22%7D"]

				</div>
				<p class="" style="text-align: center; font-size: 20px; margin-top: 15px; color: #ffffff;"><em>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</em></p>
				<p class="" style="text-align: center; margin-top: 40px;"><a class="button-primary" href="#">Learn More</a>
				</p>
			</div>
		</div>
	</div>
</div>
EOD;

return $content;

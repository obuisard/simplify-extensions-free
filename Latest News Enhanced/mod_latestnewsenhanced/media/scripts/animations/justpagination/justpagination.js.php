<?php
/**
* @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

// No direct access to this file
defined('_JEXEC') or die;

// Explicitly declare the type of content
//header("Content-type: text/javascript; charset=UTF-8");

// DO NOT ADD COMMENTS TO THE CODE -  IT WILL PREVENT COMPRESSION
?>
document.addEventListener("readystatechange", function(event) {
	if (event.target.readyState === "complete") {
		var pagination = new purePajinate({
			containerSelector: '<?php echo $css_prefix ?> .latestnews-items',
			itemSelector: '<?php echo $css_prefix ?> .latestnews-item',
			navigationSelector: '<?php echo $css_prefix ?> .items_pagination',
			wrapAround: true,
			pageLinksToDisplay: <?php echo $num_links ?>,
			itemsPerPage: <?php echo $visibleatonce ?>,
			onInit: function() {
				var news = document.querySelector("<?php echo $css_prefix ?>.newslist");
				if (news.classList) { news.classList.add("show"); } else { news.className += " " + className; }

				<?php if ($pagination_style && $bootstrap_version >= 3) : ?>
	        		var pagination = document.querySelectorAll("<?php echo $css_prefix ?> .items_pagination ul");
	        		for (var j = 0; j < pagination.length; j++) {
		        		if (pagination[j].classList) { pagination[j].classList.add("pagination"); } else { pagination[j].className += " pagination"; }
		        		<?php if ($pagination_size) : ?>
		        			if (pagination[j].classList) { pagination[j].classList.add("<?php echo $pagination_size ?>"); } else { pagination[j].className += " <?php echo $pagination_size ?>"; }
		            	<?php endif; ?>
		        		<?php if ($bootstrap_version >= 4) : ?>
		                	<?php if ($pagination_align) : ?>
		                		if (pagination[j].classList) { pagination[j].classList.add("<?php echo $pagination_align ?>"); } else { pagination[j].className += " <?php echo $pagination_align ?>"; }
		                    <?php endif; ?>
		                    var li_items = pagination[j].querySelectorAll("li");
		                    for (var i = 0; i < li_items.length; i++) {
		                    	if (li_items[i].classList) { li_items[i].classList.add("page-item"); } else { li_items[i].className += " page-item"; }
		                    	var item_link = li_items[i].querySelector("a");
		                    	if (item_link != undefined) {
		                    		if (item_link.classList) { item_link.classList.add("page-link"); } else { item_link.className += " page-link"; }
		                    	}
		                    }
		        		<?php endif; ?>
		        	}
	    		<?php endif; ?>
			},
			<?php if (!$arrows) : ?>
				showPrevNext: false,
			<?php else : ?>
				<?php if (empty($prev_label)) : ?>
					<?php if (!$horizontal && $pagination_position == 'around') : ?>
						navLabelPrev: "<span class='SYWicon-arrow-up2' aria-hidden='true'></span>",
					<?php else : ?>
						navLabelPrev: "<span class='SYWicon-arrow-left2' aria-hidden='true'></span>",
					<?php endif; ?>
					navAriaLabelPrev: "<?php echo $prev_aria_label ?>",
				<?php else : ?>
					navLabelPrev: "<span><?php echo $prev_label ?></span>",
				<?php endif; ?>
				<?php if (empty($next_label)) : ?>
					<?php if (!$horizontal && $pagination_position == 'around') : ?>
						navLabelNext: "<span class='SYWicon-arrow-down2' aria-hidden='true'></span>",
					<?php else : ?>
						navLabelNext: "<span class='SYWicon-arrow-right2' aria-hidden='true'></span>",
					<?php endif; ?>
					navAriaLabelNext: "<?php echo $next_aria_label ?>"
				<?php else : ?>
					navLabelNext: "<span><?php echo $next_label ?></span>"
				<?php endif; ?>
			<?php endif; ?>
		});
	}
});
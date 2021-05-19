<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

// Explicitly declare the type of content
header("Content-type: text/css; charset=UTF-8");
?>

<?php echo $suffix; ?>.newslist {
	height: 0;
	opacity: 0;
}

<?php echo $suffix; ?>.newslist.show {
	height: auto;
  	opacity: 1;
  	transition: opacity 1000ms;
}

<?php echo $suffix; ?> .items_pagination {
	<?php if ($pagination_position == 'title') : ?>
		display: inline-block;
		<?php if ($pagination_align == 'left' || $pagination_align == 'right') : ?>
			float: <?php echo $pagination_align; ?>;
		<?php endif; ?>
	<?php else : ?>
		display: block;
		text-align: <?php echo $pagination_align; ?>;
	<?php endif; ?>
	font-size: <?php echo $pagination_specific_size; ?>em;
}

<?php echo $suffix; ?> .items_pagination ul {
	margin: 0;
	padding: 0;
}

<?php echo $suffix; ?> .items_pagination li {
	display: inline;
	list-style: none;
	cursor: pointer;
}

<?php echo $suffix; ?>.horizontal .items_pagination.left,
<?php echo $suffix; ?>.horizontal .items_pagination.right {
	position: absolute;
	top: <?php echo $pagination_offset; ?>px;
	z-index: 100;
	text-align: center;
}

<?php echo $suffix; ?>.horizontal .items_pagination.right {
	right: 0;
}

<?php echo $suffix; ?> .items_pagination.top,
<?php echo $suffix; ?> .items_pagination.up {
	margin-bottom: <?php echo $pagination_offset; ?>px;
}

<?php echo $suffix; ?> .items_pagination.bottom,
<?php echo $suffix; ?> .items_pagination.down {
	margin-top: <?php echo $pagination_offset; ?>px;
}

<?php echo $suffix; ?> .items_pagination.up .page_link,
<?php echo $suffix; ?> .items_pagination.down .page_link,
<?php echo $suffix; ?> .items_pagination.left .page_link,
<?php echo $suffix; ?> .items_pagination.right .page_link,
<?php echo $suffix; ?> .items_pagination.up .ellipse,
<?php echo $suffix; ?> .items_pagination.down .ellipse,
<?php echo $suffix; ?> .items_pagination.left .ellipse,
<?php echo $suffix; ?> .items_pagination.right .ellipse {
	display: none !important;
}

<?php echo $suffix; ?> .items_pagination.up .next_link,
<?php echo $suffix; ?> .items_pagination.left .next_link {
	display: none;
}

<?php echo $suffix; ?> .items_pagination.down .previous_link,
<?php echo $suffix; ?> .items_pagination.right .previous_link {
	display: none;
}

<?php echo $suffix; ?> .items_pagination a {
	margin: 0 5px;
}

<?php echo $suffix; ?> .items_pagination.pagination a,
<?php echo $suffix; ?> .items_pagination .pagination a {
	margin: 0;
}

<?php echo $suffix; ?> .items_pagination a:hover,
<?php echo $suffix; ?> .items_pagination a:focus {
	text-decoration: none;
}

<?php echo $suffix; ?> .items_pagination a.no_more {
	color: #999999;
	cursor: default;
}

<?php if ($symbols) : ?>
	<?php echo $suffix; ?> .items_pagination .page_link a:before {
		font-family: 'SYWfont';
		speak: none;
		font-style: normal;
		font-weight: normal;
		font-variant: normal;
		text-transform: none;
		line-height: 1;

		/* Better Font Rendering */
		-webkit-font-smoothing: antialiased;
		-moz-osx-font-smoothing: grayscale;

		content: "\e817";
	}

	<?php echo $suffix; ?> .items_pagination .page_link.active_page a:before {
		font-family: 'SYWfont';
		speak: none;
		font-style: normal;
		font-weight: normal;
		font-variant: normal;
		text-transform: none;
		line-height: 1;

		/* Better Font Rendering */
		-webkit-font-smoothing: antialiased;
		-moz-osx-font-smoothing: grayscale;

		content: "\e818";
	}
<?php endif; ?>

<?php if (!$symbols) : ?>
    <?php echo $suffix; ?> .items_pagination .page_link.active_page {
		text-decoration: underline;
    }
<?php endif; ?>

<?php if ($symbols) : ?>
    <?php echo $suffix; ?> .items_pagination .page_link span {
		display: none;
    }
<?php endif; ?>

<?php if ($arrows && !$pages && !$symbols) : ?>
	<?php echo $suffix; ?> .items_pagination .page_link,
	<?php echo $suffix; ?> .items_pagination .ellipse {
		display: none !important;
	}
<?php endif; ?>

/* extra bootstrap 2 styles for 'around' positions */
<?php if ($pagination_style && $bootstrap_version == 2) : ?>

    <?php echo $suffix; ?> .items_pagination.pagination.left ul > li:first-child > a,
    <?php echo $suffix; ?> .items_pagination.pagination.left ul > li:first-child > span,
	<?php echo $suffix; ?> .items_pagination.pagination.up ul > li:first-child > a,
	<?php echo $suffix; ?> .items_pagination.pagination.up ul > li:first-child > span {
    	border-right-width: 1px;
    	-webkit-border-top-right-radius: 4px;
    	-moz-border-radius-topright: 4px;
    	border-top-right-radius: 4px;
    	-webkit-border-bottom-right-radius: 4px;
    	-moz-border-radius-bottomright: 4px;
    	border-bottom-right-radius: 4px;
    }

    <?php echo $suffix; ?> .items_pagination.pagination-mini.left ul > li:first-child > a,
    <?php echo $suffix; ?> .items_pagination.pagination-mini.left ul > li:first-child > span,
    <?php echo $suffix; ?> .items_pagination.pagination-small.left ul > li:first-child > a,
    <?php echo $suffix; ?> .items_pagination.pagination-small.left ul > li:first-child > span,
    <?php echo $suffix; ?> .items_pagination.pagination-mini.up ul > li:first-child > a,
    <?php echo $suffix; ?> .items_pagination.pagination-mini.up ul > li:first-child > span,
    <?php echo $suffix; ?> .items_pagination.pagination-small.up ul > li:first-child > a,
    <?php echo $suffix; ?> .items_pagination.pagination-small.up ul > li:first-child > span {
    	-webkit-border-top-right-radius: 3px;
    	-moz-border-radius-topright: 3px;
    	border-top-right-radius: 3px;
    	-webkit-border-bottom-right-radius: 3px;
    	-moz-border-radius-bottomright: 3px;
    	border-bottom-right-radius: 3px;
    }

    <?php echo $suffix; ?> .items_pagination.pagination.right ul > li:last-child > a,
    <?php echo $suffix; ?> .items_pagination.pagination.right ul > li:last-child > span,
    <?php echo $suffix; ?> .items_pagination.pagination.down ul > li:last-child > a,
    <?php echo $suffix; ?> .items_pagination.pagination.down ul > li:last-child > span {
    	border-left-width: 1px;
    	-webkit-border-top-left-radius: 4px;
    	-moz-border-radius-topleft: 4px;
    	border-top-left-radius: 4px;
    	-webkit-border-bottom-left-radius: 4px;
    	-moz-border-radius-bottomleft: 4px;
    	border-bottom-left-radius: 4px;
    }

    <?php echo $suffix; ?> .items_pagination.pagination-mini.right ul > li:last-child > a,
    <?php echo $suffix; ?> .items_pagination.pagination-mini.right ul > li:last-child > span,
    <?php echo $suffix; ?> .items_pagination.pagination-small.right ul > li:last-child > a,
    <?php echo $suffix; ?> .items_pagination.pagination-small.right ul > li:last-child > span,
    <?php echo $suffix; ?> .items_pagination.pagination-mini.down ul > li:last-child > a,
    <?php echo $suffix; ?> .items_pagination.pagination-mini.down ul > li:last-child > span,
    <?php echo $suffix; ?> .items_pagination.pagination-small.down ul > li:last-child > a,
    <?php echo $suffix; ?> .items_pagination.pagination-small.down ul > li:last-child > span {
    	-webkit-border-top-left-radius: 3px;
    	-moz-border-radius-topleft: 3px;
    	border-top-left-radius: 3px;
    	-webkit-border-bottom-left-radius: 3px;
    	-moz-border-radius-bottomleft: 3px;
    	border-bottom-left-radius: 3px;
    }
<?php endif; ?>

/* extra bootstrap 3 styles for 'around' positions */
<?php if ($pagination_style && $bootstrap_version == 3) : ?>

	<?php echo $suffix; ?> .items_pagination.left .pagination > li:first-child > a,
    <?php echo $suffix; ?> .items_pagination.left .pagination > li:first-child > span,
    <?php echo $suffix; ?> .items_pagination.up .pagination > li:first-child > a,
    <?php echo $suffix; ?> .items_pagination.up .pagination > li:first-child > span {
    	-webkit-border-top-right-radius: 4px;
    	-moz-border-radius-topright: 4px;
    	border-top-right-radius: 4px;
    	-webkit-border-bottom-right-radius: 4px;
    	-moz-border-radius-bottomright: 4px;
    	border-bottom-right-radius: 4px;
    }

    <?php echo $suffix; ?> .items_pagination.left .pagination-sm > li:first-child > a,
    <?php echo $suffix; ?> .items_pagination.left .pagination-sm > li:first-child > span,
    <?php echo $suffix; ?> .items_pagination.up .pagination-sm > li:first-child > a,
    <?php echo $suffix; ?> .items_pagination.up .pagination-sm > li:first-child > span {
    	-webkit-border-top-right-radius: 3px;
    	-moz-border-radius-topright: 3px;
    	border-top-right-radius: 3px;
    	-webkit-border-bottom-right-radius: 3px;
    	-moz-border-radius-bottomright: 3px;
    	border-bottom-right-radius: 3px;
    }

	<?php echo $suffix; ?> .items_pagination.right .pagination > li:last-child > a,
	<?php echo $suffix; ?> .items_pagination.right .pagination > li:last-child > span,
	<?php echo $suffix; ?> .items_pagination.down .pagination > li:last-child > a,
	<?php echo $suffix; ?> .items_pagination.down .pagination > li:last-child > span {
		-webkit-border-top-left-radius: 4px;
    	-moz-border-radius-topleft: 4px;
    	border-top-left-radius: 4px;
    	-webkit-border-bottom-left-radius: 4px;
    	-moz-border-radius-bottomleft: 4px;
    	border-bottom-left-radius: 4px;
	}

	<?php echo $suffix; ?> .items_pagination.right .pagination-sm > li:last-child > a,
	<?php echo $suffix; ?> .items_pagination.right .pagination-sm > li:last-child > span,
	<?php echo $suffix; ?> .items_pagination.down .pagination-sm > li:last-child > a,
	<?php echo $suffix; ?> .items_pagination.down .pagination-sm > li:last-child > span {
		-webkit-border-top-left-radius: 3px;
    	-moz-border-radius-topleft: 3px;
    	border-top-left-radius: 3px;
    	-webkit-border-bottom-left-radius: 3px;
    	-moz-border-radius-bottomleft: 3px;
    	border-bottom-left-radius: 3px;
	}
<?php endif; ?>

/* extra bootstrap 4 styles for 'around' positions */
<?php if ($pagination_style && $bootstrap_version >= 4) : ?>

    <?php echo $suffix; ?> .items_pagination.left .page-item:first-child .page-link,
    <?php echo $suffix; ?> .items_pagination.up .page-item:first-child .page-link {
    	-webkit-border-top-right-radius: .25rem;
    	-moz-border-radius-topright: .25rem;
    	border-top-right-radius: .25rem;
    	-webkit-border-bottom-right-radius: .25rem;
    	-moz-border-radius-bottomright: .25rem;
    	border-bottom-right-radius: .25rem;
    }

    <?php echo $suffix; ?> .items_pagination.left .pagination-sm .page-item:first-child .page-link,
    <?php echo $suffix; ?> .items_pagination.up .pagination-sm .page-item:first-child .page-link {
    	-webkit-border-top-right-radius: .2rem;
    	-moz-border-radius-topright: .2rem;
    	border-top-right-radius: .2rem;
    	-webkit-border-bottom-right-radius: .2rem;
    	-moz-border-radius-bottomright: .2rem;
    	border-bottom-right-radius: .2rem;
    }

    <?php echo $suffix; ?> .items_pagination.right .page-item:last-child .page-link,
    <?php echo $suffix; ?> .items_pagination.down .page-item:last-child .page-link {
    	-webkit-border-top-left-radius: .25rem;
    	-moz-border-radius-topleft: .25rem;
    	border-top-left-radius: .25rem;
    	-webkit-border-bottom-left-radius: .25rem;
    	-moz-border-radius-bottomleft: .25rem;
    	border-bottom-left-radius: .25rem;
    }

    <?php echo $suffix; ?> .items_pagination.right .pagination-sm .page-item:last-child .page-link,
    <?php echo $suffix; ?> .items_pagination.down .pagination-sm .page-item:last-child .page-link {
    	-webkit-border-top-left-radius: .2rem;
    	-moz-border-radius-topleft: .2rem;
    	border-top-left-radius: .2rem;
    	-webkit-border-bottom-left-radius: .2rem;
    	-moz-border-radius-bottomleft: .2rem;
    	border-bottom-left-radius: .2rem;
    }
<?php endif; ?>
<?php
if(Director::isDev()) {
	SSViewer::flush_template_cache();
}

Requirements::set_suffix_requirements(false);
Requirements::set_combined_files_enabled(false);

// FulltextSearchable::enable();
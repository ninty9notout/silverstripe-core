<% cached 'imagegallery', ID, List(File).max(LastEdited), List(File).count() %>
	<section id="image-gallery">
		<h2 class="heading"><span>Image Gallery</span></h2>

		<ul class="unstyled columns quarter-gutters">
		<% loop Images %>
			<li class="all-33 $FirstLast"><a href="$SetRatioSize(800, 600).URL" rel="image-gallery" title="$Title.XML">$CroppedImage(640, 395)</a></li>
		<% end_loop %>
		</ul>
	</section><!-- #image-gallery -->
<% end_cached %>
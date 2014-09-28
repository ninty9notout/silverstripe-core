<% cached 'videogallery', ID, List(Video).max(LastEdited), List(Video).count() %>
	<section id="video-gallery">
		<h2 class="heading"><span>Video Gallery</span></h2>

		<ul class="unstyled columns quarter-gutters">
		<% loop Videos %>
			<li class="all-33 $FirstLast"><a href="$IFrameURL" rel="video-gallery" title="$Title.XML">$Thumbnail.CroppedImage(640, 395)</a></li>
		<% end_loop %>
		</ul>
	</section><!-- #video-gallery -->
<% end_cached %>
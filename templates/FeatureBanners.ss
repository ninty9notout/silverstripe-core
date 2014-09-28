<% cached 'featurebanners', List(FeatureBanner).max(LastEdited), List(FeatureBanner).count() %>
	<section id="feature-banners">
		<div class="slides">
		<% loop FeatureBanners %>
			<article class="slide <% if HasPrefix %>has-prefix<% end_if %> <% if HasSuffix %>has-suffix<% end_if %>" style="background-image: url($Image.CroppedImage(1200, 500).URL);">
				<div class="grid">
					<div class="columns horizontal-gutters">
						<div class="all-33 tablet-66 mobile-100 vertical-padding">
							<% if HasPrefix %><p class="prefix">$Prefix</p><% end_if %>

							<h2><a href="$Link">$Title.XML</a></h2>

							<% if HasSuffix %><p class="suffix">$Suffix</p><% end_if %>

							<% if HasDescription %><p class="description">$Description.Summary(40)</p><% end_if %>
						</div>
					</div>
				</div>
			</article>
		<% end_loop %>
		</div>
	</section><!-- #feature-banners -->
<% end_cached %>
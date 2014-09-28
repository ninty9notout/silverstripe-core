<% cached 'relatedpages', List(RelatedPage).max(LastEdited), List(RelatedPage).count() %>
	<section id="related-pages" class="page-listing" itemscope itemtype="http://schema.org/ItemList">
		<h2 class="heading"><span itemprop="name">Related Pages</span></h2>

		<div class="columns horizontal-gutters">
		<% loop RelatedPages %>
			<article class="all-100 tablet-50" itemprop="itemListElement" itemscope itemtype="http://schema.org/WebPage">
				<div class="columns half-horizontal-gutters">
					<a href="$Link" class="all-33 desktop-40" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
					<% if MainImage %>
						$MainImage.CroppedImage(229, 129)
						<% if MainImage.Content %><meta itemprop="caption" content="$MainImage.Content.XML"><% end_if %>
					<% else_if PlaceholderImage %>
						$PlaceholderImage.CroppedImage(229, 129)
					<% end_if %>
					</a>

					<div class="all-66 desktop-60">
						<h3><a href="$Link" itemprop="headline">$Title.XML</a></h3>
						<meta itemprop="url" content="$AbsoluteLink">
						<meta itemprop="description" content="<% if MetaDescription %>$MetaDescription.XML<% else %>$Content.Summary.XML<% end_if %>">
						$Breadcrumbs
					</div>
				</div>
			</article>
		<% end_loop %>
		</div>
	</section><!-- #related-pages -->
<% end_cached %>
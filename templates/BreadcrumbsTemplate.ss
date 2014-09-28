<% cached 'breadcrumbs', ID, LastEdited %>
	<% if ClassName != Homepage && Pages %>
	<ul class="breadcrumbs" itemprop="breadcrumb">
		<li><a href="$BaseHref"><i class="fa fa-home"></i><span> Home</span></a></li>
		<% loop Pages %>
			<% if $Last %>
				<li class="active"><a>$MenuTitle.XML</a></li>
			<% else %>
				<li><a href="$Link">$MenuTitle.XML</a></li>
			<% end_if %>
		<% end_loop %>
	</ul><!-- .breadcrumbs -->
	<% end_if %>
<% end_cached %>
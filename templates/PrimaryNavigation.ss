<a id="toggle-nav"><i class="fa fa-bars"></i></a>

<% cached 'primarynavigation', ID, LastEdited %>
	<nav id="primary-navigation" role="navigation">
		<ul class="unstyled">
		<% loop $Menu(1) %>
			<li class="$LinkingMode"><a href="$Link">$MenuTitle.XML</a></li>
		<% end_loop %>
		</ul>
	</nav>
<% end_cached %>
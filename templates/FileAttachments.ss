<% cached 'fileattachments', ID, List(File).max(LastEdited), List(File).count() %>
	<section id="file-attachments">
		<h2 class="heading"><span>Attachments</span></h2>

		<ul class="unstyled">
		<% loop Files %>
			<li class="$FirstLast"><a href="$Link" class="$Extension" target="_blank">
				<span>Name: </span>$Title<br>
				<em><span>Size: </span>$Size - <span>Date uploaded: </span>$Created.Ago</em>
			</a></li>
		<% end_loop %>
		</ul>
	</section><!-- #file-attachments -->
<% end_cached %>
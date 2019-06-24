<%-- used in gridfield --%>
<% if $Image %>
	<% with $Image %>
        <img src="$URL" alt="$Title" style="max-width:80px;max-height: 60px;">
	<% end_with %>
<% end_if %>
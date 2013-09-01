<?php
		//ye olde news feed
		
		
		$User = $_SESSION['SkateBoard']['user'];
		
		//Tree-style comments
		function get_comments($parentID , $topLevel = false)
		{
			global $db;
			$sql = "SELECT * FROM `comments` JOIN `users` USING(login) WHERE parentID = $parentID AND toplevel = ".($topLevel ? 1 : 0);
			$comments = $db->query($sql)->fetch_all(MYSQLI_ASSOC);
			
			if(!empty($comments))
			foreach($comments as $key => $value)
			{
				$comments[$key]['comments'] = get_comments($value['commentID']);
			}
			
			return $comments;
			
		}
		
		function draw_comment($comments, $indent)
		{
			foreach($comments as $com)
			{
				echo "<div class='commentBox' style='margin-left: $indent".'px'."'>";
				echo $com['content']." <br /><b>".$com['first']." ".$com['last']."</b> <span class='greyed'>".$com['time']."</span> <a href='javascript:;' onclick='comBox(".$com['commentID'].",0)'>comment</a>";
				echo "</div>";
				if(isset($com['comments']) && !empty($com['comments']))
					draw_comment($com['comments'],$indent+20);
			}
		}
		
		//arbitrary posts-to-appear limit
		$limit = 30;
		$sql = "SELECT * FROM `feed` JOIN `users` USING(login) ORDER BY postID DESC LIMIT $limit";
		$posts = $db->query($sql)->fetch_all(MYSQLI_ASSOC);
		
		echo "<div id='feedBox' class='contentBox'>";
			//var_export($posts);
			
			foreach($posts as $post)
			{
				$coms = get_comments($post['postID'], true);
				//now draw it
				echo "<div class='postBox'>";
				echo "<div class='postHeader'>";
				echo "Posted by ".$post['first']." ".$post['last']." at ".$post['time'];
				echo "</div><div class='postContent'>";
				echo $post['content'];
				
				echo "</div><div class='postFooter'>";
				echo "<a href='javascript:;'  onclick='comBox(".$post['postID'].",1)'>[Post Comment]</a>";
				echo "</div><div class='postComment'>";
				//comment stuff
				if(!empty($coms))
					echo "<hr />";
				draw_comment($coms,1);
				echo "</div></div>";
				
				
			}
			
		echo "</div>";
		
		
		//Text Box for Posting Comments
		echo "<div id='textBox' style='display: NONE'>";
		//echo "<div id='textBox'>";
		echo "Comment as ".$User.":<br />";
		echo "<textarea id='textBoxField'></textarea><br />";
		echo "<a href='javascript:;' onclick='document.getElementById(\"textBox\").style.display = \"NONE\" '>[cancel]</a>";
		echo "<input type='button' name='Post' value='Post' style='float: right' onclick='submitComment()'>";
		
		
		echo "</div>";
		
		echo "<script type='text/javascript'>currentUser='$User'</script>";
		
	
?>

<script src="./js/jquery-1.10.2.min.js"></script>
<script type="text/javascript">
   function comBox(id,toplevel)
   {
      document.getElementById("textBox").style.display = "block";
	  parentID = id;
	  topLevelComment = toplevel;
   }
   function submitComment()
   {
		var commentText = document.getElementById("textBoxField").value;
		$.ajax({
			url: "AJAXcontroller.php",
			data: {
				user: currentUser,
				action: "submitComment",
				parent: parentID,
				content: commentText,
				toplevel: topLevelComment
			},
			type: "POST",
			error: function(XHR, textStatus, errorThrown) { console.log(textStatus); console.log(errorThrown)}
		});
		document.getElementById("textBox").style.display = "NONE";
   }
</script>
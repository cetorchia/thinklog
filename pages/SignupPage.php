<?php
require_once(DOC_ROOT . "/pages/Page.php");

/**
 * Also takes care of the processing of the request to signup in the
 * POST params.
 */

class SignUpPage extends Page
{
	public function getContent()
	{
		$skipSignupForm = false;
		$output = "";

		$POST = $this->serverRequest->getPOST();

		// Get what update to do from the POST request.
		$signup = isset($POST["signup"]) && ($POST["signup"] != '0');
		$thinkerId = isset($POST["thinkerId"]) ? $POST["thinkerId"] : null;
		$name = isset($POST["name"]) ? $POST["name"] : null;
		$password = isset($POST["password"]) ? $POST["password"] : null;
		$passwordConfirm = isset($POST["passwordConfirm"]) ? $POST["passwordConfirm"] : null;
		$about = isset($POST["about"]) ? $POST["about"] : null;

		//
		// If POST parameters indicate a signup request, then (try to) sign up.
		//

		if($signup)
		{
			$output .= "<div class=\"section\">\n";
			if($password != $passwordConfirm)
			{
				$output .= "<b>Your password does not match the re-type.</b>\n";
			}

			else
			{
				// Make the thinker
				$thinker = new Thinker();
				$thinker->setId($thinkerId);
				$thinker->setName($name);
				$thinker->setAbout($about);

				if($this->services->thinkerService->add($thinker,sha1($password)))
				{
					$output .= "Thinker creation successful: you may now log in.";
					$skipSignupForm = true;
				}
				else {
					$output .= "<b>Could not create thinker ".htmlentities($thinkerId).": ";
					$output .= "Error occurred: ".htmlentities(mysql_error());
				}
			}
			$output .= "</div>\n";
		}

		else if(isset($login))
		{
			$output .= "<div class=\"section\"><b>You don't need another account!</b></div>\n";
			$skipSignupForm = true;
		}

		if(!$skipSignupForm)
		{
			$output .= "<div id=\"sign_up\" class=\"section\">\n";
			$output .= "<h2>Sign up</h2>\n";
			$output .= "<form method=\"post\" action=\"./?signup\">\n";
			$output .= "<input type=\"hidden\" name=\"signup\" value=\"1\" />\n";
			$output .= "<table>\n";
			$output .= "<tr>\n";
			$output .= "<td>Thinker ID:</td>\n";
			$output .= "<td><input type=\"text\" name=\"thinkerId\" maxlength=\"64\" /><br />\n";
			$output .= "<i>e.g. \"juan\" or \"maria\"</i></td>\n";
			$output .= "</tr><tr>\n";
			$output .= "<td>Your name:</td>\n";
			$output .= "<td><input type=\"text\" name=\"name\" maxlength=\"64\" /><br />\n";
			$output .= "<i>This name people will see.</i></td>\n";
			$output .= "</tr><tr>\n";
			$output .= "<td>Password:</td>\n";
			$output .= "<td><input type=\"password\" name=\"password\" /><br />\n";
			$output .= "<i>Choose a strong password.</i></td>\n";
			$output .= "</tr><tr>\n";
			$output .= "<td>Re-type Password:</td>\n";
			$output .= "<td><input type=\"password\" name=\"passwordConfirm\" />\n";
			$output .= "</tr><tr>\n";
			$output .= "<td>About:</td>\n";
			$output .= "<td><textarea name=\"about\" row=\"3\" cols=\"40\"></textarea><br />\n";
			$output .= "<i>(max 512 characters)</i><br /></td>\n";
			$output .= "</tr>\n";
			$output .= "<tr><td><input type=\"submit\" value=\"create thinker\" /></td></tr>\n";
			$output .= "</table>\n";
			$output .= "</form>\n";
			$output .= "</div>\n";
		}

		return $output;
	}
}

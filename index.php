<?php
/**
Plugin Name: Video & Vimeo
Plugin URI: ...
Description: Allows users to upload their videos to Vimeo
Version: 1.0
Author: Anita Aksentowicz
Author URI: #
License: GPLv2 or later
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: vid-vimupload
*/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Vimeo\Vimeo;
// Styles & Fonts
function dot_vimeo_scripts() {
    wp_enqueue_style( 'dot-vimeo-css', plugins_url( '/css/style.css' , __FILE__ ) );
    wp_enqueue_script( 'dot-vimeo-js', plugins_url( '/app.js' , __FILE__ ), array( 'jquery' )  );
    
  }

add_action( 'wp_enqueue_scripts', 'dot_vimeo_scripts' );

require __DIR__.'/vendor/vimeo/vimeo-api/autoload.php';
require_once('vendor/autoload.php');


function do_t_vimeo() {

    $CLIENT_ID = "your-id";
    $CLIENT_SECRET = "your-secret";
    $ACCESS_TOKEN = "your-access";

// Initialize the Vimeo API client with your app credentials and access token

$client = new Vimeo($CLIENT_ID, $CLIENT_SECRET, $ACCESS_TOKEN);

// Handle form submission if ($_SERVER['REQUEST_METHOD'] == 'POST') {
if ( isset($_POST["do-t-video-upload-user"])) {
  $videoFile = $_FILES['video-file'];
  $videoName = $_POST['video-name'];
  $videoDescription = $_POST['video-description'];
  $videoPrivacy = $_POST['video-privacy'];

  // Upload the video to Vimeo
  $uploadResponse = $client->upload(
    $videoFile['tmp_name'],
    [
      'name' => $videoName,
      'description' => $videoDescription,
      'privacy' => [
        'view' => $videoPrivacy,
        'embed' => 'whitelist'
      ]
    ]
  );
print_r($uploadResponse);
  // Get the video URI from the upload response
  $videoUri = $uploadResponse;

  if ($uploadResponse) {
    echo '<p>Upload successful!</p>';
  }
  
}

?>
<section>
<div class="bottom">
  <h1>Step 1</h1>
  <h3>Record your video here:</h3>
      <pre id="log"></pre>
    </div>
      <div id="startButton" class="button">Start Recording</div>
 <div id="stopButton" class="button">Stop Recording</div>
 <a id="downloadButton" class="button"> Download </a>
<div id="prev">
      <h2>Preview</h2>
      <video id="preview" width="300" height="300" autoplay muted></video> 
</div>
<div id="reco">
	 <h2>Recording</h2>
      <video id="recording" width="300" height="300" controls></video>
</div>
</section>  
<style>
video {
    margin-top: 2px;
    border: 1px solid black;
    }
    .button {
    cursor: pointer;
    display: block;
    width: 160px;
    border: 1px solid black;
    font-size: 16px;
    text-align: center;
    padding-top: 2px;
    padding-bottom: 4px;
    color: white;
    background-color: darkgreen;
    text-decoration: none;
    }
    h2 {
    margin-bottom: 4px;
    }
    .bottom {
    clear: both;
    padding-top: 10px;
    }
	#stopButton{
		display: none;
	}
	#downloadButton{
		display: none;
	}
	#prev{
		display: none;
	}
	#reco{
		display: none;
	}

  section{
    margin-bottom: 50px;
  }
</style>
      <script>
      let preview = document.getElementById("preview");
      let recording = document.getElementById("recording");
      let startButton = document.getElementById("startButton");
      let stopButton = document.getElementById("stopButton");
      let downloadButton = document.getElementById("downloadButton");
      let logElement = document.getElementById("log");
	  let fileInput = document.getElementById("video-file");
      let recorder;

      function log(msg) {
        logElement.innerHTML += `${msg}\n`;
      }

      function startRecording(stream) {
        recorder = new MediaRecorder(stream);
        let data = [];

        recorder.ondataavailable = (event) => data.push(event.data);
        recorder.start();
        log(`${recorder.state}`);

        return new Promise((resolve) => {
          stopButton.addEventListener("click", () => {
			  stopButton.style.display = "none";
			  prev.style.display = "none";
downloadButton.style.display = "block";
			  reco.style.display = "block";
            recorder.onstop = () => {
              let recordedBlob = new Blob(data, { type: "video/webm" });
              recording.src = URL.createObjectURL(recordedBlob);
              downloadButton.href = recording.src;
              downloadButton.download = "RecordedVideo.webm";
			
              log(
                `Successfully recorded ${recordedBlob.size} bytes of ${recordedBlob.type} media.`
              );
              resolve(recorder);
            };
            recorder.stop();
			  
          });
        });
      }

      function stop(stream) {
        stream.getTracks().forEach((track) => track.stop());
      }

      startButton.addEventListener(
        "click",
        () => {
			startButton.style.display = "none";
stopButton.style.display = "block";
			prev.style.display = "block";
          navigator.mediaDevices
            .getUserMedia({
              video: true,
              audio: true,
            })
            .then((stream) => {
              preview.srcObject = stream;
              downloadButton.href = stream;
              preview.captureStream =
                preview.captureStream || preview.mozCaptureStream;
              return new Promise((resolve) => (preview.onplaying = resolve));
            })
            .then(() => startRecording(preview.captureStream()))
            .then((recorder) => {
              stop(preview.srcObject);
            })
            .catch((error) => {
              if (error.name === "NotFoundError") {
                log("Camera or microphone not found. Can't record.");
              } else {
                log(error);
              }
            });
        },
        false
      );
		  
    </script>
    <section>
<h1>Step 2</h1>
  <h3>Upload you video:</h3>
  <form action="" method="post" enctype="multipart/form-data">
    <label for="video-file">Video file:</label>
    <input type="file" id="video-file" name="video-file" required><br>

    <label for="video-name">Video name:</label>
    <input type="text" id="video-name" name="video-name" required><br>

    <label for="video-description">Video description:</label>
    <textarea id="video-description" name="video-description" required></textarea><br>

    <label for="video-privacy">Video privacy:</label>
    <select id="video-privacy" name="video-privacy">
      <option value="anybody">Public</option>
      <option value="nobody">Private</option>
    </select><br>

    <button type="submit" name="do-t-video-upload-user">Upload</button>
  </form>
  </section>

<?php
}
//create shortcode
add_shortcode('do_t_vimeo', 'do_t_vimeo');



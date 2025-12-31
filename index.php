<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>フックマークDB - 剛の拳</title>
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/pose/pose.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js"></script>
  <style>
    body { background-color: #000; color: #fff; font-family: "Hiragino Mincho ProN", serif; overflow: hidden; }
    
    /* 謝罪ポップアップ */
    #apology-overlay {
      position: fixed; top: 0; left: 0; width: 100%; height: 100%;
      background: #000; z-index: 2000; display: flex; align-items: center; justify-content: center;
    }
    .apology-content {
      text-align: center; border: 2px solid #fff; padding: 40px; max-width: 500px; background: #111;
    }
    .btn-forgive {
      background: #fff; color: #000; border: none; padding: 10px 40px; margin-top: 20px;
      font-weight: bold; cursor: pointer;
    }

    /* メイン */
    #main-dojo { display: none; }

    .main-container { margin-top: 10px; text-align: center; }
    h1 { font-size: 2.8rem; color: #e60000; letter-spacing: 0.8rem; text-shadow: 0 0 10px #ff0000; }
    #guide-text { font-size: 1.6rem; background: #1a1a1a; border-left: 10px solid #e60000; padding: 12px; margin: 10px auto; width: 90%; max-width: 600px; color: #d4af37; }
    #video-container { position: relative; display: inline-block; border: 4px solid #d4af37; background: #111; }
    #webcam { transform: scaleX(-1); width: 100%; max-width: 580px; height: auto; }

    /* 測定ポップアップ */
    #result-overlay {
      display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
      background: rgba(0, 0, 0, 0.95); border: 4px solid #d4af37; padding: 40px;
      z-index: 1000; width: 85%; max-width: 450px; text-align: center;
    }
    .measuring-text { font-size: 2.5rem; color: #d4af37; animation: blink 0.5s infinite; }
    .velocity-res { font-size: 5.5rem; color: #e60000; font-weight: bold; margin: 15px 0; }
    .btn-next { background: #e60000; color: #fff; border: none; padding: 15px 40px; font-size: 1.5rem; cursor: pointer; }
    
    @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }
  </style>
</head>
<body>

<div id="apology-overlay">
  <div class="apology-content">
    <p style="font-size: 1.2rem; line-height: 2;">
      申し訳ございません。<br>
      ブックマークDBではなく、<br>
      <strong style="color: #e60000; font-size: 1.5rem;">フックマークDB</strong>を<br>
      作ってしまいました。
    </p>
    <button class="btn-forgive" onclick="forgive()">許 す</button>
  </div>
</div>

<div id="main-dojo">
    <div class="container main-container">
      <h1>フックマークDB</h1>
      <div id="guide-text">精神を統一中...</div>
      <div id="video-container">
        <video id="webcam" autoplay playsinline></video>
      </div>
      <div><a href="select.php" style="color:#d4af37; text-decoration:none; border:1px solid; padding:5px 10px; display:inline-block; margin-top:10px;">≫ 戦績（グラフ）を確認する</a></div>
    </div>

    <div id="result-overlay">
      <div id="measuring-box">
          <div class="measuring-text">判定中...</div>
          <p style="color:#d4af37;">拳圧を解析しています</p>
      </div>
      <div id="result-box" style="display:none;">
          <p style="color:#ccc;">測定結果</p>
          <div class="velocity-res"><span id="speed-val">0</span> <small>km/h</small></div>
          <button class="btn-next" onclick="resetForNext()">次 の 測 定 へ</button>
      </div>
    </div>
</div>

<script>
// 「許す」をクリックした時の処理
function forgive() {
    document.getElementById('apology-overlay').style.display = 'none';
    document.getElementById('main-dojo').style.display = 'block';
    startCamera(); // 許された後にカメラを起動
}

const videoElement = document.getElementById('webcam');
const guideElement = document.getElementById('guide-text');
const overlay = document.getElementById('result-overlay');
const measuringBox = document.getElementById('measuring-box');
const resultBox = document.getElementById('result-box');
const speedVal = document.getElementById('speed-val');

let isPunching = false;
let isWaitingNext = false;
let lastWristX = 0; 

const pose = new Pose({
  locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/pose/${file}`
});

pose.setOptions({ modelComplexity: 1, smoothLandmarks: true, minDetectionConfidence: 0.7, minTrackingConfidence: 0.7 });

pose.onResults((results) => {
  if (isWaitingNext || !results.poseLandmarks) return;
  const landmarks = results.poseLandmarks;
  const rightWrist = landmarks[16];   
  const leftShoulder = landmarks[11]; 
  const rightShoulder = landmarks[12];
  const deltaX = lastWristX - rightWrist.x;
  lastWristX = rightWrist.x;

  if (rightWrist.x > rightShoulder.x + 0.05) {
    guideElement.textContent = "構えよし。右フックを放て！";
    guideElement.style.color = "#00ff00";
  } else {
    guideElement.textContent = "右手を引き、構えを作れ";
    guideElement.style.color = "#d4af37";
  }

  if (deltaX > 0.06 && rightWrist.x < leftShoulder.x && !isPunching) {
    isPunching = true;
    triggerMeasurement();
  }
});

function startCamera() {
    const camera = new Camera(videoElement, {
      onFrame: async () => { await pose.send({image: videoElement}); },
      width: 640, height: 480
    });
    camera.start();
}

function triggerMeasurement() {
  isWaitingNext = true; 
  overlay.style.display = "block";
  measuringBox.style.display = "block";
  resultBox.style.display = "none";
  setTimeout(() => { showFinalResult(); }, 1400);
}

async function showFinalResult() {
  const velocity = (Math.random() * 60 + 135).toFixed(1);
  speedVal.textContent = velocity;
  measuringBox.style.display = "none";
  resultBox.style.display = "block";
  const formData = new FormData();
  formData.append('punch_type', 'HookMark');
  formData.append('velocity', velocity);
  formData.append('comment', '剛腕の一撃');
  await fetch('insert.php', { method: 'POST', body: formData });
}

function resetForNext() {
  overlay.style.display = "none";
  isWaitingNext = false;
  isPunching = false;
}
</script>
</body>
</html>
// ---------- Matrix rain background ----------
const canvas = document.getElementById("matrix");
const ctx = canvas.getContext("2d");
const chars = "01アイウエオカキクケコサシスセソタチツテトABCDEF<>/{}[]";
let columns, drops;

function resizeCanvas() {
  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;
  columns = Math.floor(canvas.width / 16);
  drops = Array(columns).fill(1);
}

function drawMatrix() {
  ctx.fillStyle = "rgba(0, 0, 0, 0.08)";
  ctx.fillRect(0, 0, canvas.width, canvas.height);
  ctx.fillStyle = "#00ff41";
  ctx.font = "16px monospace";

  for (let i = 0; i < drops.length; i++) {
    const text = chars.charAt(Math.floor(Math.random() * chars.length));
    ctx.fillText(text, i * 16, drops[i] * 16);
    if (drops[i] * 16 > canvas.height && Math.random() > 0.975) {
      drops[i] = 0;
    }
    drops[i]++;
  }
}

window.addEventListener("resize", resizeCanvas);
resizeCanvas();
setInterval(drawMatrix, 50);

// ---------- Boot sequence ----------
const bootLines = [
  "Initializing secure shell...",
  "Loading kernel modules........ OK",
  "Establishing encrypted tunnel... OK",
  "Scanning for intrusions....... CLEAR",
  "Identity verified: " + window.HAROLD_USER,
  "Welcome to the system."
];

const bootLog = document.getElementById("boot-log");
const menu = document.getElementById("main-menu");
const output = document.getElementById("output");

function typeLine(text, container, speed = 25) {
  return new Promise((resolve) => {
    const p = document.createElement("p");
    p.className = "line";
    container.appendChild(p);
    let i = 0;
    const timer = setInterval(() => {
      p.textContent += text.charAt(i);
      i++;
      if (i >= text.length) {
        clearInterval(timer);
        resolve();
      }
    }, speed);
  });
}

async function runBoot() {
  for (const line of bootLines) {
    await typeLine(line, bootLog);
    await new Promise((r) => setTimeout(r, 200));
  }
  menu.classList.remove("hidden");
}

runBoot();

// ---------- Menu commands ----------
const responses = {
  scan: [
    "Scanning local network...",
    "192.168.1.1   ....  ROUTER    [OPEN]",
    "192.168.1.24  ....  WORKSTATION [SECURE]",
    "192.168.1.87  ....  UNKNOWN   [WARNING]",
    "Scan complete. 3 hosts found."
  ],
  decrypt: [
    "Accessing vault...",
    "Brute-forcing AES-256 key...",
    "Progress: [##########..........] 48%",
    "Access denied. Vault is locked by admin."
  ],
  trace: [
    "Tracing route to target...",
    "hop 1  ->  10.0.0.1      12 ms",
    "hop 2  ->  172.16.4.9    34 ms",
    "hop 3  ->  203.0.113.7   58 ms",
    "Trace complete."
  ],
  logs: [
    "Reading system logs...",
    "[05:12:01] Login success: " + window.HAROLD_USER,
    "[05:13:44] Firewall rule updated",
    "[05:20:09] No threats detected."
  ]
};

async function runCommand(cmd) {
  output.textContent = "";
  const lines = responses[cmd] || ["Unknown command."];
  for (const line of lines) {
    await typeLine(line, output, 15);
  }
}

document.querySelectorAll(".menu a[data-cmd]").forEach((link) => {
  link.addEventListener("click", (e) => {
    e.preventDefault();
    runCommand(link.dataset.cmd);
  });
});
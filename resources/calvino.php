<!DOCTYPE html>
<html>
<head>
    <title>PharmaSys by Calvino Pro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2980b9, #6dd5fa);
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }
        .container {
            text-align: center;
            animation: float 3s ease-in-out infinite;
            padding: 20px;
            width: 90%;
            max-width: 600px;
        }
        h1 {
            color: white;
            font-size: 3.5rem;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.3);
            margin: 0;
            line-height: 1.2;
        }
        .pro {
            color: #ff4757;
            font-weight: bold;
            position: relative;
            display: inline-block;
        }
        .pro:after {
            content: "✨";
            position: absolute;
            top: -15px;
            right: -15px;
            animation: sparkle 1.5s linear infinite;
        }
        .pills {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
        .pill {
            position: absolute;
            background: rgba(255,255,255,0.5);
            border-radius: 50px;
            animation: fall linear infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        @keyframes sparkle {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }
        @keyframes fall {
            0% { transform: translateY(-100px) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            100% { transform: translateY(100vh) rotate(360deg); opacity: 0; }
        }
        
        /* Media Queries for Responsiveness */
        @media (max-width: 768px) {
            h1 {
                font-size: 2.5rem;
            }
            .pro:after {
                top: -10px;
                right: -10px;
            }
        }
        
        @media (max-width: 480px) {
            h1 {
                font-size: 2rem;
            }
            .container {
                padding: 15px;
            }
            .pro:after {
                top: -8px;
                right: -8px;
                font-size: 0.8em;
            }
        }
        
        @media (max-height: 500px) {
            .container {
                transform: scale(0.8);
            }
        }
    </style>
</head>
<body>
    <div class="pills" id="pills"></div>
    <div class="container">
        <h1>PharmaSys by Calvino <span class="pro">Pro</span></h1>
    </div>
    <script>
        // Create animated pills in the background
        const pillsContainer = document.getElementById("pills");
        const createPills = () => {
            // Clear existing pills
            pillsContainer.innerHTML = '';
            
            // Number of pills based on screen size
            const pillCount = window.innerWidth < 480 ? 10 : 20;
            
            for (let i = 0; i < pillCount; i++) {
                const pill = document.createElement("div");
                pill.classList.add("pill");
                
                // Random properties
                const size = Math.random() * 30 + 10;
                const posX = Math.random() * 100;
                const delay = Math.random() * 5;
                const duration = Math.random() * 10 + 5;
                
                pill.style.width = `${size}px`;
                pill.style.height = `${size/2}px`;
                pill.style.left = `${posX}%`;
                pill.style.animationDelay = `${delay}s`;
                pill.style.animationDuration = `${duration}s`;
                
                pillsContainer.appendChild(pill);
            }
        };
        
        // Initial creation
        createPills();
        
        // Recreate pills on window resize
        window.addEventListener('resize', () => {
            // Debounce to avoid excessive recreation
            clearTimeout(window.resizeTimer);
            window.resizeTimer = setTimeout(createPills, 250);
        });
    </script>
</body>
</html>

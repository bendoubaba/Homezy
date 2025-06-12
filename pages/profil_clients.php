<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Providers</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            text-align: center;
        }
        h1 {
            font-size: 2rem;
            margin: 20px 0;
            color: #370d0d;
        }
        .providers {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .provider-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 59px;
            text-align: center;
        }
        .provider-card img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
        }
        .provider-name {
            font-size: 1.2rem;
            color: #555;
            margin-bottom: 10px;
        }
        .provider-title {
            font-size: 1rem;
            color: #888;
            margin-bottom: 15px;
        }
        .rating {
            color: #ff9800;
        }
        footer {
    text-align: center;
    padding: 140px;
padding-top: 50px;
    color: rgb(68, 20, 20);
    margin-top: 30px;
}
header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 10%;
    font-size: 22px;
    background-color: #ffffff; /* Soft orange for a welcoming vibe */
    animation: fadeIn 1.5s ease-in-out;
}

header .logo {
    font-size: 24px;
    font-weight: bold;
    color: #ef325e; /* Vibrant pink for a standout brand name */
}
header .name {
    font-size: 34px;
    font-weight: bold;
    color: #53081a; /* Vibrant pink for a standout brand name */
}
header nav ul {
    list-style: none;
    display: flex;
    gap: 20px;
}

header nav ul li a {
    text-decoration: none;
    color: #130802; /* Brown for links */
    font-weight: 500;
    position: relative;
}

header nav ul li a:hover {
    color: #FF9F1C; /* Bright orange hover effect */
}
footer {
    background-color: #f7f5f5;
    color: rgb(17, 3, 3);
    text-align: center;
    padding: 20px;
    width: 100%;
    margin-top: 30px;
  }

  footer .footer-content {
    display: flex;
    justify-content: space-between;
    max-width: 80%;
    margin: auto;
  }

  footer a {
    color: rgb(59, 16, 16);
    text-decoration: none;
    margin-right: 15px;
  }

  footer a:hover {
    text-decoration: underline;
  }
    </style>
</head>
<body>
    <header>
        <div class="logo">Homezy</div>
        
          <nav>
            <ul>
              <li><a href="index.html">Home</a></li>
              <li><a href="contact.html">Contact</a></li>
              <li><a href="gerer_profil.html">
                <span class="emoji"></span> Profile
              </a></li>
              <li><a href="#">Notification</a></li>
            </ul>
          </nav>
        </div>
      </header>
    </div>
    </header>
    <h1 id="page-title"></h1>
    <div class="providers" id="providers-container"></div>
    <script>
      const pageTitle = "Service Provider Profiles";
      document.getElementById('page-title').textContent = pageTitle;
  
      const providers = [
          { name: "John Doe", image: "https://via.placeholder.com/100" },
          { name: "Jane Smith", image: "https://via.placeholder.com/100" },
          { name: "Alex Brown", image: "https://via.placeholder.com/100" },
          { name: "Emily White", image: "https://via.placeholder.com/100" }
      ];
  
      const providersContainer = document.getElementById('providers-container');
      providers.forEach(provider => {
          const providerCard = document.createElement('div');
          providerCard.className = 'provider-card';
  
          providerCard.innerHTML = `
              <img src="${provider.image}" alt="Provider Image">
              <div class="provider-name">${provider.name}</div>
            
          `;
  
          // Rendre toute la carte cliquable
          providerCard.style.cursor = "pointer";
          providerCard.onclick = function() {
              window.location.href = `profil.html?name=${encodeURIComponent(provider.name)}`;
          };
  
          providersContainer.appendChild(providerCard);
      });
  </script>
  
  
  
    <footer>
        <div class="footer-content">
          <div class="footer-contact">
            <h3>Contact Us</h3>
            <p><strong>Phone:</strong> +213 635 546 513</p>
            <p><strong>Email:</strong> support@homezy.com</p>
          </div>
          <div class="footer-social">
            <h3>Follow Us</h3>
            <div class="social-links">
              <a href="https://facebook.com" target="_blank">Facebook</a>
              <a href="https://twitter.com" target="_blank">Twitter</a>
              <a href="https://instagram.com" target="_blank">Instagram</a>
            </div>
          </div>
        </div>
        <p>&copy; 2025 Homezy. All rights reserved.</p>
      </footer>
</body>
</html>

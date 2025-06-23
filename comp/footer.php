<!DOCTYPE html>
<html>
<head>
</head>
<body>
    <style>
        .footer_section {
            display: flex;
            flex-direction: column;
            justify-content: center;
            margin: 10px;
            background-color: transparent;
        }

        .footer_section nav {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
            color: #888; /* Adjust the color as needed */
            font-weight: bold;
            margin-top: 10px;
        }

        .footer_section nav a {
            color: white !important;
        }

        .footer_section nav a:hover {
            color: red; /* Adjust the color as needed */
        }

        .footer_section .social-icons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .footer_section .social-icons a img {
            width: 30px;
            height: 30px;
        }

        .footer_section .copyright {
            text-align: center;
            color: #888; /* Adjust the color as needed */
            font-weight: bold;
        }


        .social-icons {
            margin-top: 20px;
        }

        .copyright {
            margin-top: 20px;

        }
        
        .pe {
            color: white !important;
        }
        .imgs {
            size: 10px !important;
            height: 20px;
            width: 20px !important;
            margin-right: 3px;
            margin-bottom: 5px;
        }
    </style>
    
<footer class="bg-transparent text-white p-4 mt-3 d-flex justify-content-center align-items-center flex-column">
    <nav class="navbar navbar-expand navbar-dark bg-transparent p-2 mb-0">
        <ul class="navbar-nav flex-wrap d-flex justify-content-center align-items-center">
          
            <li class="nav-item">
                <a class="nav-link fs-5" href="../shop/terms.php">TOS</a>
            </li> 
            <li class="nav-item">
                <a class="nav-link fs-5" href="../shop/faqs.php">FAQs</a>
            </li>
        </ul>
    </nav>
<p id="copyright" class="m-auto text-center mt-2 text-white fs-6"></p>
</footer>


</body>
<script>
    var currentYear = new Date().getFullYear();

    // Fetch shop name from the server
    fetch('https://poland.fo/get_shop_name.php') // Replace 'get_shop_name.php' with the actual script to retrieve the shop name
        .then(response => response.text())
        .then(shopName => {
            document.getElementById("copyright").innerHTML = "© " + currentYear + "   " + shopName + " , All rights reserved";
        })
        .catch(error => {
            console.error('Error fetching shop name: ', error);
        });
</script>

</html>

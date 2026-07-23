<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Module</title>
    <style>
      :root {
        --primary: #4a90e2;
        --secondary: #27ae60;
        --bg: #f0f4f8;
        --card: #ffffff;
        --text: #2c3e50;
      }
      body {
        margin: 0; display: flex; justify-content: center; align-items: center;
        min-height: 100vh; font-family: 'Segoe UI', sans-serif;
        background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);
      }
      .container {
        background: var(--card); padding: 30px; border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1); width: 350px; text-align: center;
      }
      h2 { color: var(--text); margin-bottom: 20px; }
      input { padding: 12px; width: 90%; margin-bottom: 15px; border: 2px solid #e1e8ed; border-radius: 10px; }
      button { 
        padding: 12px 25px; background: var(--primary); color: white; border: none; 
        border-radius: 10px; cursor: pointer; font-weight: 600; transition: transform 0.2s;
      }
      button:hover { background: #357abd; transform: translateY(-2px); }
      #result { 
        margin-top: 20px; text-align: left; background: #f8f9fa; 
        padding: 15px; border-radius: 10px; border-left: 4px solid var(--primary); 
      }
      .hidden { display: none; }
      #loading { color: #555; font-style: italic; }
    </style>
  </head>
  <body>
    <div class="container">
      <h2>Supervisor Module</h2>
      <div id="loading">Loading data...</div>
      
      <div id="searchBox" class="hidden">
        <input type="text" id="userInput" placeholder="Enter User ID">
        <button onclick="searchUser()">Search Profile</button>
      </div>
      
      <div id="result" class="hidden"></div>

      <div id="censusSection" class="hidden" style="margin-top: 20px;">
        <hr>
        <label><strong>Census House Number:</strong></label><br><br>
        <input type="text" id="censusInput" placeholder="Enter Number...">
        <br>
        <button onclick="saveCensus()" style="background: var(--secondary);">Submit Entry</button>
      </div>
    </div>

    <script>
      let userData = [];
      let currentUserId = "";

      // Load initial list from PHP backend proxy
      fetch('backend-api.php?action=getUserData')
        .then(response => response.json())
        .then(data => {
          userData = data;
          document.getElementById('loading').classList.add('hidden');
          document.getElementById('searchBox').classList.remove('hidden');
        })
        .catch(error => {
          console.error('Error loading data:', error);
          document.getElementById('loading').innerText = "Error loading system data.";
        });

      function searchUser() {
        const input = document.getElementById("userInput").value.trim();
        const resultDiv = document.getElementById("result");
        const user = userData.find(u => u.id === input);

        if (user) {
          currentUserId = user.id;
          resultDiv.classList.remove('hidden');
          
          let html = `<p><strong>Functionary:</strong> ${user.typeOfFunctionary}</p>
                      <p><strong>Name:</strong> ${user.name}</p>
                      <p><strong>District/M.Corp.:</strong> ${user.district}</p>
                      <p><strong>Sub-District:</strong> ${user.subDistrict}</p>`;
          
          if (user.town && user.town.toString().trim() !== "") {
            html += `<p><strong>Town:</strong> ${user.town}</p>`;
          }
          
          resultDiv.innerHTML = html;
          document.getElementById('censusSection').classList.remove('hidden');
        } else {
          resultDiv.classList.remove('hidden');
          resultDiv.innerHTML = "<span style='color:#e74c3c;'>User ID not found.</span>";
          document.getElementById('censusSection').classList.add('hidden');
        }
      }

      function saveCensus() {
        const houseNo = document.getElementById("censusInput").value.trim();
        if(!houseNo) { alert("Please enter a House No."); return; }
        
        fetch('backend-api.php?action=saveCensusData', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ userId: currentUserId, houseNo: houseNo })
        })
        .then(response => response.json())
        .then(result => {
          if(result.success) {
            alert("Successfully saved!");
            document.getElementById("censusInput").value = "";
            document.getElementById("userInput").value = "";
            document.getElementById("result").classList.add('hidden');
            document.getElementById("censusSection").classList.add('hidden');
          } else {
            alert("Error saving data: " + (result.message || "Unknown error"));
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert("Network error while trying to save.");
        });
      }
    </script>
  </body>
</html>
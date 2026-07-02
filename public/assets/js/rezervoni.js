document.getElementById('rezervimForm').addEventListener('submit', async function (e) {
  e.preventDefault();

  const sherbimi = document.getElementById('sherbimi').value;
  if (sherbimi === "") {
    document.getElementById('statusi').innerText = "⚠️ Ju lutem zgjidhni një shërbim!";
    return;
  }

  const rezervim = {
    emri: document.getElementById('emri').value.trim(),
    mbiemri: document.getElementById('mbiemri').value.trim(),
    email: document.getElementById('email').value.trim(),
    data: document.getElementById('data').value,
    ora: document.getElementById('ora').value,
    sherbimi: sherbimi
  };

  try {
    const response = await fetch('api/submit_reservation.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(rezervim)
    });

    if (response.ok) {
      const resJson = await response.json().catch(()=>({}));
      document.getElementById('statusi').innerText = resJson.message || '✅ Rezervimi u krye me sukses!';
      document.getElementById('rezervimForm').reset();
    } else {
      const err = await response.json().catch(()=>({message:'Dështoi rezervimi.'}));
      console.error("Gabim nga serveri:", err);
      document.getElementById('statusi').innerText = `❌ ${err.message || 'Dështoi rezervimi.'}`;
    }
  } catch (error) {
    document.getElementById('statusi').innerText = '❌ Gabim në lidhje me serverin.';
    console.error("Gabim teknik:", error);
  }
});

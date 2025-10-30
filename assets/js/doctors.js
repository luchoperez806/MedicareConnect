document.addEventListener("DOMContentLoaded", function () {
  const especialidadSelect = document.getElementById("especialidad");
  const doctorList = document.getElementById("doctor-list");

  // Función para obtener los médicos desde PHP (AJAX)
  async function cargarDoctores() {
    const response = await fetch("includes/load_doctors.php");
    const data = await response.json();

    mostrarDoctores(data);

    // Llenar especialidades
    const especialidades = [...new Set(data.map(d => d.specialization))];
    especialidades.forEach(esp => {
      const option = document.createElement("option");
      option.value = esp;
      option.textContent = esp;
      especialidadSelect.appendChild(option);
    });

    // Filtrar por especialidad
    especialidadSelect.addEventListener("change", () => {
      const filtro = especialidadSelect.value;
      const filtrados = filtro ? data.filter(d => d.specialization === filtro) : data;
      mostrarDoctores(filtrados);
    });
  }

  function mostrarDoctores(doctores) {
    doctorList.innerHTML = "";
    if (doctores.length === 0) {
      doctorList.innerHTML = "<p>No hay médicos disponibles en esta especialidad.</p>";
      return;
    }

    doctores.forEach(doctor => {
      const card = document.createElement("div");
      card.classList.add("doctor-card");

      card.innerHTML = `
        <img src="uploads/${doctor.profile_pic || 'default.png'}" alt="Foto" class="doctor-img">
        <h3>${doctor.doctorname}</h3>
        <p><strong>Especialidad:</strong> ${doctor.specialization}</p>
        <p><strong>Consultorio:</strong> ${doctor.office_address}</p>
        <div class="map-container">
          <iframe
            width="100%"
            height="200"
            frameborder="0"
            style="border:0; border-radius:10px; margin-top:10px;"
            src="https://www.google.com/maps?q=${encodeURIComponent(doctor.office_address)}&output=embed"
            allowfullscreen>
          </iframe>
        </div>
      `;

      doctorList.appendChild(card);
    });
  }

  cargarDoctores();
});

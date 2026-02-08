<?php
include '../dashboard/nav.php';
require_once '../conexion/conexion.php';
?>
<link rel="stylesheet" href="ventas.css">
<div class="carrito-page">
<div class="content-header d-flex justify-content-between align-items-center">
  <h2><i class="fa-solid fa-cart-shopping text-warning"></i> Carrito de Venta</h2>
  <button class="btn btn-outline-warning btn-sm" id="btnVaciar">
    <i class="fa-solid fa-trash"></i> Vaciar carrito
  </button>
</div>

<div class="content-body mt-3">
  <div class="card shadow-sm p-3 modulo">
    <table id="tablaCarrito" class="table table-dark table-hover table-sm align-middle mb-0">
      <thead>
        <tr>
          <th style="width:60px">Img</th>
          <th>Producto</th>
          <th style="width:100px" class="text-center">Cant.</th>
          <th style="width:120px" class="text-end">Precio</th>
          <th style="width:120px" class="text-end">Subtotal</th>
          <th style="width:60px"></th>
        </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
  <tr>
    <th colspan="4" class="text-end">Total:</th>
    <th id="totalCarrito" class="text-glow text-end"></th>
  </tr>

  <tr>
    <td colspan="5" class="text-end">
      <span id="totalUSD" class="text-info fw-bold me-3"></span>
      <span id="totalARS" class="text-warning fw-bold"></span>
    </td>
  </tr>
</tfoot>

    </table>

    <div class="mt-3 text-end">
      <button class="btn btn-success btnConfirmar">
        <i class="fa-solid fa-check"></i> Confirmar Venta
      </button>
    </div>
  </div>
</div>
</div>
<script>



/* ============================================================
   METADATA: Comprobantes, métodos de pago y monedas
============================================================ */
let METADATA = {
    comprobantes: [],
    metodos_pago: [],
    monedas: []
};

async function cargarMetadataVentas() {
    try {
        const r = await fetch('/motoshoppy/api/get_metadata_ventas.php');
        const d = await r.json();

        METADATA.comprobantes = d.comprobantes;
        METADATA.metodos_pago = d.metodos_pago;
        METADATA.monedas = d.monedas;
        METADATA.cotizacion = d.cotizacion;  // ← FALTABA ESTO

        console.log("Metadata cargada:", METADATA);
    } catch (err) {
        console.error("Error cargando metadata:", err);
    }
}


cargarMetadataVentas();

/* ============================================================
   Formatear moneda
============================================================ */
const money = v => Number(v || 0).toLocaleString('es-AR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
});



/* ============================================================
   Renderizar carrito
============================================================ */
function renderCarrito() {
    const carrito = JSON.parse(localStorage.getItem("carrito") || "[]");
    const tbody = document.querySelector("#tablaCarrito tbody");
    tbody.innerHTML = "";
    let total = 0;

    if (carrito.length === 0) {
        tbody.innerHTML = `
        <tr>
            <td colspan="6" class="empty-cart">
                <i class="fa-solid fa-cart-arrow-down"></i><br>
                <span>Tu carrito está vacío</span>
            </td>
        </tr>`;
        document.getElementById("totalCarrito").textContent = "₲ 0";

        document.getElementById("totalUSD").textContent = "≈ $ 0,00 USD";
        document.getElementById("totalARS").textContent = "≈ $ 0,00 ARS";
        return;
    }

    /* =======================================================
       PRIMERO SUMAMOS EL TOTAL
    ======================================================= */
    carrito.forEach((p, i) => {
        const subtotal = p.precio_expuesto * p.cantidad;
        total += subtotal;

        const imgSrc = p.imagen
            ? `/motoshoppy/${String(p.imagen).replace(/\\\\/g, "/").replace(/^\/+/, "")}`
            : "/motoshoppy/imagenes/noimg.png";

        tbody.innerHTML += `
        <tr>
            <td><img src="${imgSrc}" class="img-fluid rounded-circle mini-img"></td>

            <td>
                <div class="fw-semibold">${p.nombre}</div>
                <div class="small text-secondary">
                    ${p.codigo ? `Cod: ${p.codigo}` : ""}
                    ${p.nombre_marca ? ` · Marca: ${p.nombre_marca}` : ""}
                    ${p.modelo ? ` · Modelo: ${p.modelo}` : ""}
                    ${p.nombre_categoria ? ` · Cat: ${p.nombre_categoria}` : ""}
                </div>

                ${
                    p.stock_estado
                        ? `<span class="badge ${
                              p.stock_estado === "ok"
                                  ? "bg-success"
                                  : p.stock_estado === "bajo_stock"
                                  ? "bg-warning text-dark"
                                  : "bg-danger"
                          }">Stock: ${p.stock_actual ?? "-"}</span>`
                        : ""
                }
            </td>

            <td class="text-center">
                <input type="number" min="1" value="${p.cantidad}" 
                    class="form-control form-control-sm text-center qtyInput"
                    data-index="${i}">
            </td>

            <td class="text-end">₲ ${money(p.precio_expuesto)}</td>
            <td class="text-end fw-bold text-warning">₲ ${money(subtotal)}</td>

            <td class="text-center">
                <button class="btn btn-sm btn-outline-danger" onclick="eliminarDelCarrito(${i})">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        </tr>`;
    });

    /* =======================================================
       ACTUALIZAMOS EL TOTAL EN GUARANÍES
    ======================================================= */
    document.getElementById("totalCarrito").textContent = `₲ ${money(total)}`;

    /* =======================================================
       AHORA QUE TOTAL YA ESTÁ SUMADO → CONVERTIMOS USD/ARS
    ======================================================= */
    const cot = METADATA.cotizacion;

    const totalUSD = cot?.usd_pyg > 0 ? total / cot.usd_pyg : 0;
    const totalARS = cot?.ars_pyg > 0 ? total / cot.ars_pyg : 0;

    document.getElementById("totalUSD").textContent = `≈ $ ${money(totalUSD)} USD`;
    document.getElementById("totalARS").textContent = `≈ $ ${money(totalARS)} ARS`;

    /* =======================================================
       INPUTS DINÁMICOS
    ======================================================= */
    document.querySelectorAll(".qtyInput").forEach(inp => {
        inp.addEventListener("change", e => {
            const carrito = JSON.parse(localStorage.getItem("carrito") || "[]");
            const idx = parseInt(e.target.dataset.index);
            carrito[idx].cantidad = Math.max(1, parseInt(e.target.value) || 1);
            localStorage.setItem("carrito", JSON.stringify(carrito));
            renderCarrito();
        });
    });
}


/* ============================================================
   CRUD Carrito
============================================================ */
function eliminarDelCarrito(i) {
    const carrito = JSON.parse(localStorage.getItem("carrito") || "[]");
    carrito.splice(i, 1);
    localStorage.setItem("carrito", JSON.stringify(carrito));
    renderCarrito();
}

document.getElementById("btnVaciar").addEventListener("click", () => {
    Swal.fire({
        icon: "warning",
        title: "¿Vaciar carrito?",
        text: "Se eliminarán todos los productos.",
        showCancelButton: true,
        confirmButtonText: "Sí, vaciar"
    }).then(r => {
        if (r.isConfirmed) {
            localStorage.removeItem("carrito");
            renderCarrito();
        }
    });
});

/* ============================================================
   Confirmar venta
============================================================ */
document.querySelector(".btnConfirmar").addEventListener("click", async () => {

    const carrito = JSON.parse(localStorage.getItem("carrito") || "[]");
    if (carrito.length === 0) {
        Swal.fire({ icon: "warning", title: "Carrito vacío" });
        return;
    }

    const total = carrito.reduce((a, b) => a + b.precio_expuesto * b.cantidad, 0);

    /* ----------------------------
       Paso 1: Tipo comprobante
    ---------------------------- */
    let optsComp = {};
    METADATA.comprobantes.forEach(c => optsComp[c.id] = c.nombre);

    const { value: comprobanteID } = await Swal.fire({
        title: "Tipo de comprobante",
        input: "radio",
        inputOptions: optsComp,
        inputValue: Object.keys(optsComp)[0],
        showCancelButton: true
    });

    if (!comprobanteID) return;

    const compData = METADATA.comprobantes.find(c => c.id == comprobanteID);
    const esFactura = compData.nombre.toLowerCase().includes("factura");

    /* ----------------------------
       Paso 2: Método + Moneda + Cliente
    ---------------------------- */
    let htmlMetodo = `<option disabled selected value="">Seleccioná método</option>`;
    METADATA.metodos_pago.forEach(m => {
        htmlMetodo += `<option value="${m.id}">${m.nombre}</option>`;
    });

    let htmlMoneda = `<option disabled selected value="">Seleccioná moneda</option>`;
    METADATA.monedas.forEach(m => {
        htmlMoneda += `<option value="${m.id}">${m.codigo} - ${m.nombre}</option>`;
    });

    const htmlPago = `
        <label class="fw-bold mt-2">Método de Pago</label>
        <select id="metodoPago" class="form-select">${htmlMetodo}</select>

        <div id="otroMetodo" class="mt-2 d-none">
            <input id="otroTexto" class="form-control" placeholder="Describí el método...">
        </div>

        <hr>

        <label class="fw-bold">Moneda (solo efectivo)</label>
        <select id="monedaPago" class="form-select d-none">${htmlMoneda}</select>

        <hr>

        ${esFactura ? `
            <label class="fw-bold">Datos del Cliente (Factura)</label>
            <input id="cliNombreFactura" class="form-control mb-2" placeholder="Nombre">
            <input id="cliApellidoFactura" class="form-control mb-2" placeholder="Apellido">
            <input id="cliDniFactura" class="form-control mb-2" placeholder="DNI">
            <input id="cliCelularFactura" class="form-control mb-2" placeholder="Celular">
        ` : `
            <label class="fw-bold">DNI Cliente (Ticket)</label>
            <input id="cliDniTicket" class="form-control mb-2" placeholder="DNI">
        `}
    `;

    const { value: confirmar } = await Swal.fire({
        title: "Confirmar venta",
        html: `
            <p><strong>Total:</strong> ₲ ${money(total)}</p>
            ${htmlPago}
        `,
        width: 600,
        showCancelButton: true,
        confirmButtonText: "Finalizar venta",
        didOpen: () => {
            const metodoSel = document.getElementById("metodoPago");
            const monedaSel = document.getElementById("monedaPago");

            metodoSel.addEventListener("change", () => {
                const m = METADATA.metodos_pago.find(x => x.id == metodoSel.value);

                document.getElementById("otroMetodo")
                    .classList.toggle("d-none", m.nombre.toLowerCase() !== "otro");

                monedaSel.classList.toggle("d-none", m.nombre.toLowerCase() !== "efectivo");
            });

            /* === Autocompletar cliente para factura === */
            if (esFactura) {
                const dniInput = document.getElementById("cliDniFactura");

                dniInput.addEventListener("input", async e => {
                    const dni = e.target.value.trim();
                    if (dni.length < 6) return;

                    try {
                        const r = await fetch(`/motoshoppy/ventas/api_buscar_cliente.php?dni=${dni}`);
                        const d = await r.json();

                        if (d.ok && d.cliente) {
                            document.getElementById("cliNombreFactura").value = d.cliente.nombre;
                            document.getElementById("cliApellidoFactura").value = d.cliente.apellido;
                            document.getElementById("cliCelularFactura").value = d.cliente.celular;
                            dniInput.classList.add("is-valid");
                        } else {
                            dniInput.classList.remove("is-valid");
                        }
                    } catch (err) {
                        console.error(err);
                    }
                });
            }
        },
        preConfirm: () => {
            const metodoID = document.getElementById("metodoPago").value;
            if (!metodoID) return Swal.showValidationMessage("Seleccioná método de pago");

            const metodoData = METADATA.metodos_pago.find(x => x.id == metodoID);
            let monedaID = "";

            if (metodoData.nombre.toLowerCase() === "efectivo") {
                monedaID = document.getElementById("monedaPago").value;
                if (!monedaID) return Swal.showValidationMessage("Seleccioná la moneda");
            }

            let cliente = null;

            if (esFactura) {
                const n = document.getElementById("cliNombreFactura").value.trim();
                const a = document.getElementById("cliApellidoFactura").value.trim();
                const dni = document.getElementById("cliDniFactura").value.trim();

                if (!n || !a || !dni) return Swal.showValidationMessage("Completá todos los datos del cliente");

                cliente = { nombre: n, apellido: a, dni };
            } else {
                const dni = document.getElementById("cliDniTicket").value.trim();
                if (!dni) return Swal.showValidationMessage("Ingresá DNI del cliente");
                cliente = { dni };
            }

            return { metodo_pago: metodoID, moneda: monedaID, cliente };
        }
    });

    if (!confirmar) return;

    /* ----------------------------
       Enviar al backend
    ---------------------------- */
    const payload = {
        tipo_comprobante: comprobanteID,
        metodo_pago: confirmar.metodo_pago,
        moneda: confirmar.moneda || null,
        productos: carrito,
        total,
        cliente: confirmar.cliente
    };

    try {
        const r = await fetch("/motoshoppy/ventas/api_comprar.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        });

        const data = await r.json();

        if (data.ok) {
            Swal.fire({
                icon: "success",
                title: "Venta registrada",
                text: `Venta #${data.venta_id}`,
                timer: 1600,
                showConfirmButton: false
            });

            localStorage.removeItem("carrito");
            renderCarrito();

            /* ----------------------------
               ABRIR TICKET O FACTURA
            ---------------------------- */
            const dni = payload.cliente?.dni || "";

            if (parseInt(payload.tipo_comprobante) === 1) {
                window.open(`/motoshoppy/ventas/generar_ticket.php?id=${data.venta_id}&dni=${encodeURIComponent(dni)}`, "_blank");
            }

            if (parseInt(payload.tipo_comprobante) === 2) {
                window.open(`/motoshoppy/ventas/generar_factura.php?id=${data.venta_id}`, "_blank");
            }

        } else {
            Swal.fire({ icon: "error", title: "Error", text: data.msg });
        }

    } catch (err) {
        console.error(err);
        Swal.fire({ icon: "error", title: "Error de conexión" });
    }
});

/* ============================================================
   Inicializar
============================================================ */
document.addEventListener("DOMContentLoaded", async () => {
    await cargarMetadataVentas();  // esperar metadata
    renderCarrito();               // ahora sí renderiza con cotización cargada
});

</script>



<?php include '../dashboard/footer.php'; ?>

<?php

return [

    /* =========================
       CATEGORIAS
    ========================= */
    'categoria' => [
        'campos' => [
            'nombre_categoria' => 'Nombre',
            'descripcion'      => 'Descripción',
            'estado'           => 'Estado'
        ],
        'valores' => [
            'estado' => [
                1 => 'Activa',
                0 => 'Inactiva'
            ]
        ]
    ],

    /* =========================
       CLIENTES
    ========================= */
    'clientes' => [
        'campos' => [
            'apellido' => 'Apellido',
            'nombre'   => 'Nombre',
            'dni'      => 'DNI',
            'celular'  => 'Celular',
            'email'    => 'Email',
            'estado'   => 'Estado'
        ],
        'valores' => [
            'estado' => [
                'activo'   => 'Activo',
                'inactivo' => 'Inactivo'
            ]
        ]
    ],

    /* =========================
       MARCAS
    ========================= */
    'marcas' => [
        'campos' => [
            'nombre_marca'       => 'Marca',
            'categoria_idCategoria' => 'Categoría',
            'estado'             => 'Estado'
        ],
        'valores' => [
            'estado' => [
                1 => 'Activa',
                0 => 'Inactiva'
            ]
        ]
    ],

    /* =========================
       LISTAS DE PRECIO
    ========================= */
    'precio_lista' => [
        'campos' => [
            'nombre_lista'         => 'Lista',
            'porcentaje_descuento' => 'Descuento (%)',
            'activo'               => 'Estado'
        ],
        'valores' => [
            'activo' => [
                1 => 'Activa',
                0 => 'Inactiva'
            ]
        ]
    ],

    /* =========================
       COTIZACIÓN
    ========================= */
    'cotizacion' => [
        'campos' => [
            'usd_ars' => 'USD → ARS',
            'usd_pyg' => 'USD → PYG',
            'ars_pyg' => 'ARS → PYG',
            'fuente'  => 'Fuente'
        ]
    ],

    /* =========================
       DETALLE DE VENTA
    ========================= */
    'detalle_venta' => [
        'campos' => [
            'producto_idProducto' => 'Producto',
            'cantidad'            => 'Cantidad',
            'precio_unitario'     => 'Precio unitario',
            'subtotal'            => 'Subtotal',
            'devuelto'            => 'Devuelto'
        ],
        'valores' => [
            'devuelto' => [
                0 => 'No',
                1 => 'Sí'
            ]
        ]
    ],

    /* =========================
       DEVOLUCIONES
    ========================= */
    'devoluciones_venta' => [
        'campos' => [
            'producto_idProducto' => 'Producto',
            'cantidad'            => 'Cantidad',
            'motivo'              => 'Motivo'
        ]
    ],

    /* =========================
       MOVIMIENTO DE STOCK
    ========================= */
    'movimiento_stock' => [
        'campos' => [
            'producto_idProducto' => 'Producto',
            'cantidad'            => 'Cantidad',
            'tipo'                => 'Tipo de movimiento'
        ],
        'valores' => [
            'tipo' => [
                'ingreso' => 'Ingreso',
                'egreso'  => 'Egreso'
            ]
        ]
    ],

    /* =========================
       ATRIBUTOS CUBIERTAS
    ========================= */
    'atributos_cubiertas' => [
        'campos' => [
            'aro'               => 'Aro',
            'ancho'             => 'Ancho',
            'perfil_cubierta'   => 'Perfil',
            'tipo'              => 'Tipo',
            'varias_aplicaciones' => 'Varias aplicaciones'
        ],
        'valores' => [
            'varias_aplicaciones' => [
                0 => 'No',
                1 => 'Sí'
            ]
        ]
    ],

    'producto' => [
        'campos' => [
            'Categoria_idCategoria' => 'Categoría',
            'codigo'               => 'Código',
            'nombre'               => 'Nombre',
            'marcas_idmarcas'       => 'Marca',
            'modelo'               => 'Modelo',
            'peso_ml'              => 'Peso (ml)',
            'descripcion'          => 'Descripción',
            'precio_costo'         => 'Precio costo',
            'precio_expuesto'      => 'Precio venta',
            'ubicacion_producto'   => 'Ubicación',
            'activo'               => 'Estado'
        ],
        'valores' => [
            'activo' => [
                0 => 'Inactivo',
                1 => 'Activo'
            ]
        ]
    ],

    /* =========================
       PROVEEDORES
    ========================= */
    'proveedores' => [
        'campos' => [
            'empresa'          => 'Empresa',
            'ubicacion'        => 'Ubicación',
            'telefono'         => 'Teléfono',
            'email'            => 'Email',
            'activo'           => 'Estado',
            'vendedor'         => 'Vendedor',
            'numero_vendedor'  => 'N° vendedor'
        ],
        'valores' => [
            'activo' => [
                0 => 'Inactivo',
                1 => 'Activo'
            ]
        ]
    ],

    /* =========================
       REPOSICIÓN (PEDIDOS)
    ========================= */
    'reposicion' => [
        'campos' => [
            'proveedores_idproveedores' => 'Proveedor',
            'estado'                    => 'Estado',
            'forma_pago'                => 'Forma de pago',
            'observacion'               => 'Observación',
            'fecha_pedido'              => 'Fecha pedido',
            'fecha_llegada'             => 'Fecha llegada',
            'costo_total'               => 'Costo total',
            'numero_factura'            => 'Factura',
            'numero_recibo'             => 'Recibo'
        ],
        'valores' => [
            'estado' => [
                'pedido'    => 'Pedido',
                'impactado' => 'Impactado',
                'cancelado' => 'Cancelado'
            ],
            'forma_pago' => [
                'contado'  => 'Contado',
                'credito'  => 'Crédito'
            ]
        ],
        'especial' => [
            'proveedores_idproveedores' => [
                'tabla' => 'proveedores',
                'pk'    => 'idproveedores',
                'campo' => 'empresa'
            ]
        ]
    ],

    /* =========================
       FACTURA DE PROVEEDOR (CUENTA CORRIENTE)
    ========================= */
    'factura_proveedor' => [
        'campos' => [
            'proveedores_idproveedores' => 'Proveedor',
            'reposicion_idreposicion'   => 'Reposición',
            'fecha_compra'              => 'Fecha compra',
            'descripcion'               => 'Descripción',
            'monto'                     => 'Monto',
            'numero_factura'            => 'Factura',
            'tiene_factura'             => 'Tiene factura',
            'estado_pago'               => 'Estado de pago',
            'monto_pagado'              => 'Monto pagado'
        ],
        'valores' => [
            'estado_pago' => [
                'pendiente' => 'Pendiente',
                'parcial'   => 'Parcial',
                'pagado'    => 'Pagado'
            ],
            'tiene_factura' => [
                0 => 'No',
                1 => 'Sí'
            ]
        ],
        'especial' => [
            'proveedores_idproveedores' => [
                'tabla' => 'proveedores',
                'pk'    => 'idproveedores',
                'campo' => 'empresa'
            ],
            'reposicion_idreposicion' => [
                'tabla' => 'reposicion',
                'pk'    => 'idreposicion',
                'campo' => 'idreposicion'
            ]
        ]
    ],

    /* =========================
       PAGOS A PROVEEDOR (CUENTA CORRIENTE)
    ========================= */
    'pagos_factura_proveedor' => [
        'campos' => [
            'factura_proveedor_idFacturaProveedor' => 'Factura',
            'fecha_pago'                           => 'Fecha de pago',
            'monto'                                => 'Monto',
            'numero_comprobante'                   => 'N° Comprobante'
        ]
    ],

    /* =========================
       DETALLE DE REPOSICIÓN
    ========================= */
    'reposicion_detalle' => [
        'campos' => [
            'reposicion_idreposicion' => 'Reposición',
            'producto_idProducto'     => 'Producto',
            'cantidad'                => 'Cantidad',
            'costo'                   => 'Costo',
            'codigo_proveedor'        => 'Código proveedor'
        ],
        'especial' => [
            'producto_idProducto' => [
                'tabla' => 'producto',
                'pk'    => 'idProducto',
                'campo' => 'nombre'
            ],
            'reposicion_idreposicion' => [
                'tabla' => 'reposicion',
                'pk'    => 'idreposicion',
                'campo' => 'idreposicion'
            ]
        ]
    ],


/* =========================
   USUARIO_ROLES
========================= */
'usuario_roles' => [
    'campos' => [
        'roles' => 'Roles',
    ],
    'especial' => [
        'roles' => [
            'tabla' => 'roles',
            'pk'    => 'idroles',
            'campo' => 'nombre_rol',
        ],
    ],
],

    /* =========================
       STOCK PRODUCTO
    ========================= */
    'stock_producto' => [
        'campos' => [
            'producto_idProducto' => 'Producto',
            'stock_minimo'        => 'Stock mínimo',
            'cantidad_actual'     => 'Cantidad actual',
            'cantidad_exhibida'   => 'Cantidad exhibida'
        ]
    ],

    /* =========================
       TIPO COMPROBANTE
    ========================= */
    'tipo_comprobante' => [
        'campos' => [
            'nombre' => 'Tipo de comprobante'
        ]
    ],

    /* =========================
       UBICACIÓN PRODUCTO
    ========================= */
    'ubicacion_producto' => [
        'campos' => [
            'lugar'   => 'Lugar',
            'estante' => 'Estante'
        ]
    ],

    /* =========================
       USUARIO
    ========================= */
    'usuario' => [
        'campos' => [
            'nombre'   => 'Nombre',
            'apellido' => 'Apellido',
            'dni'      => 'DNI',
            'celular'  => 'Celular',
            'usuario'  => 'Usuario',
            'avatar'   => 'Avatar'
        ]
    ],


    /* =========================
       VENTAS
    ========================= */
    'ventas' => [
        'campos' => [
            'fecha'                         => 'Fecha',
            'total'                         => 'Total',
            'observaciones'                 => 'Observaciones',
            'metodo_pago_idmetodo_pago'     => 'Método de pago',
            'tipo_comprobante_idtipo_comprobante' => 'Tipo de comprobante',
            'clientes_idCliente'            => 'Cliente',
            'usuario_idusuario'             => 'Usuario',
            'moneda_idmoneda'               => 'Moneda'
        ]
    ],

    /* =========================
       VENTAS ANULADAS
    ========================= */
    'ventas_anuladas' => [
        'campos' => [
            'ventas_idVenta'     => 'Venta',
            'producto_idProducto'=> 'Producto',
            'cantidad_devuelta'  => 'Cantidad devuelta',
            'motivo'             => 'Motivo',
            'usuario_idusuario'  => 'Usuario',
            'fecha'              => 'Fecha'
        ]
    ],


];

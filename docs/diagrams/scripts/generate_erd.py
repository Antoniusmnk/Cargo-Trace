import os
import matplotlib.pyplot as plt
import matplotlib.patches as patches

os.environ['MPLCONFIGDIR'] = os.path.abspath('docs/diagrams/.mpl_cache')
os.makedirs('docs/diagrams/.mpl_cache', exist_ok=True)

def create_erd_diagram():
    fig, ax = plt.subplots(figsize=(28, 18), dpi=200)
    ax.set_xlim(0, 28)
    ax.set_ylim(0, 18)
    ax.axis('off')

    # Background
    fig.patch.set_facecolor('#0B0F19') # Sleek dark slate
    ax.set_facecolor('#0B0F19')

    # Title Banner
    title_box = patches.FancyBboxPatch((1.0, 16.5), 26.0, 1.2,
                                       boxstyle="round,pad=0.2",
                                       edgecolor="#38BDF8", facecolor="#111827", linewidth=2)
    ax.add_patch(title_box)
    ax.text(14, 17.25, "CARGOTRACE – ENTITY RELATIONSHIP DIAGRAM (ERD)",
            color="#F8FAFC", fontsize=22, fontweight="bold", ha="center", va="center")
    ax.text(14, 16.75, "Physical Data Model (9 Core Entities) | MySQL 8+ Engine & Eloquent ORM Architecture",
            color="#94A3B8", fontsize=11, ha="center", va="center")

    # Table Box Drawing Function
    def draw_table(x, y, w, title, columns, header_color="#1E293B", badge="TABLE"):
        line_height = 0.32
        h = 0.65 + len(columns) * line_height
        
        # Outer Card
        card = patches.FancyBboxPatch((x, y - h), w, h,
                                      boxstyle="round,pad=0.08",
                                      edgecolor=header_color, facecolor="#111827", linewidth=1.8)
        ax.add_patch(card)
        
        # Header Box
        header = patches.Rectangle((x, y - 0.6), w, 0.6,
                                   edgecolor="none", facecolor=header_color)
        ax.add_patch(header)
        
        # Table Title
        ax.text(x + 0.3, y - 0.3, title,
                color="#FFFFFF", fontsize=11, fontweight="bold", va="center")
        
        # Badge
        badge_box = patches.FancyBboxPatch((x + w - 1.4, y - 0.45), 1.2, 0.3,
                                           boxstyle="round,pad=0.04",
                                           edgecolor="#FFFFFF", facecolor="#000000", linewidth=0.8, alpha=0.4)
        ax.add_patch(badge_box)
        ax.text(x + w - 0.8, y - 0.3, badge,
                color="#E2E8F0", fontsize=7.5, fontweight="bold", ha="center", va="center")
        
        # Columns
        curr_y = y - 0.82
        for col in columns:
            key_type = col.get("key", "")
            name = col.get("name", "")
            col_type = col.get("type", "")
            extra = col.get("extra", "")
            
            # Key indicator
            if key_type == "PK":
                ax.text(x + 0.25, curr_y, "[PK]", color="#FBBF24", fontsize=8, fontweight="bold", va="center")
            elif key_type == "FK":
                ax.text(x + 0.25, curr_y, "[FK]", color="#38BDF8", fontsize=8, fontweight="bold", va="center")
            elif key_type == "PFK":
                ax.text(x + 0.25, curr_y, "[PFK]", color="#F472B6", fontsize=7.5, fontweight="bold", va="center")
            else:
                ax.text(x + 0.3, curr_y, "•", color="#64748B", fontsize=9, va="center")
                
            # Column Name
            ax.text(x + 1.1, curr_y, name, color="#F8FAFC", fontsize=8.5, fontweight="600" if key_type else "normal", va="center")
            
            # Column Type & Extra
            type_str = f"{col_type} {extra}".strip()
            ax.text(x + w - 0.25, curr_y, type_str, color="#94A3B8", fontsize=7.5, ha="right", va="center")
            
            # Sublte separator line
            ax.plot([x + 0.1, x + w - 0.1], [curr_y - line_height/2, curr_y - line_height/2],
                    color="#1F2937", linewidth=0.6)
            
            curr_y -= line_height
            
        return {"x": x, "y": y, "w": w, "h": h, "bottom": y - h, "center_x": x + w/2, "center_y": y - h/2}

    # 1. CARRIERS (Master Data)
    t_carriers = draw_table(1.0, 15.8, 5.0, "carriers", [
        {"key": "PK", "name": "id", "type": "BIGINT", "extra": "UNSIGNED AI"},
        {"key": "", "name": "code", "type": "VARCHAR(50)", "extra": "UNIQUE"},
        {"key": "", "name": "name", "type": "VARCHAR(255)", "extra": ""},
        {"key": "", "name": "contact_email", "type": "VARCHAR(255)", "extra": ""},
        {"key": "", "name": "phone", "type": "VARCHAR(50)", "extra": ""},
        {"key": "", "name": "status", "type": "VARCHAR(30)", "extra": "active/inactive"},
        {"key": "", "name": "created_at", "type": "TIMESTAMP", "extra": ""},
        {"key": "", "name": "updated_at", "type": "TIMESTAMP", "extra": ""},
    ], header_color="#0D9488", badge="MASTER")

    # 2. LOCATIONS (Master Data)
    t_locations = draw_table(1.0, 11.8, 5.0, "locations", [
        {"key": "PK", "name": "id", "type": "BIGINT", "extra": "UNSIGNED AI"},
        {"key": "", "name": "code", "type": "VARCHAR(50)", "extra": "UNIQUE"},
        {"key": "", "name": "name", "type": "VARCHAR(255)", "extra": "Port/Hub"},
        {"key": "", "name": "type", "type": "VARCHAR(50)", "extra": "port/hub/wh"},
        {"key": "", "name": "address", "type": "TEXT", "extra": "NULL"},
        {"key": "", "name": "city", "type": "VARCHAR(100)", "extra": ""},
        {"key": "", "name": "country", "type": "VARCHAR(100)", "extra": ""},
        {"key": "", "name": "latitude", "type": "DECIMAL(10,7)", "extra": ""},
        {"key": "", "name": "longitude", "type": "DECIMAL(10,7)", "extra": ""},
        {"key": "", "name": "created_at", "type": "TIMESTAMP", "extra": ""},
        {"key": "", "name": "updated_at", "type": "TIMESTAMP", "extra": ""},
    ], header_color="#0D9488", badge="MASTER")

    # 3. USERS (Security & Auth)
    t_users = draw_table(21.8, 15.8, 5.2, "users", [
        {"key": "PK", "name": "id", "type": "BIGINT", "extra": "UNSIGNED AI"},
        {"key": "", "name": "name", "type": "VARCHAR(255)", "extra": ""},
        {"key": "", "name": "email", "type": "VARCHAR(255)", "extra": "UNIQUE"},
        {"key": "", "name": "password", "type": "VARCHAR(255)", "extra": "HASHED"},
        {"key": "", "name": "role", "type": "VARCHAR(30)", "extra": "admin/operator/cust"},
        {"key": "", "name": "status", "type": "VARCHAR(30)", "extra": "active/suspended"},
        {"key": "", "name": "remember_token", "type": "VARCHAR(100)", "extra": "NULL"},
        {"key": "", "name": "created_at", "type": "TIMESTAMP", "extra": ""},
        {"key": "", "name": "updated_at", "type": "TIMESTAMP", "extra": ""},
    ], header_color="#0284C7", badge="AUTH & RBAC")

    # 4. SHIPMENTS (Core Hub Entity)
    t_shipments = draw_table(9.0, 15.8, 7.8, "shipments", [
        {"key": "PK", "name": "id", "type": "BIGINT", "extra": "UNSIGNED AI"},
        {"key": "", "name": "shipment_number", "type": "VARCHAR(50)", "extra": "UNIQUE INDEX"},
        {"key": "FK", "name": "customer_id", "type": "BIGINT UNSIGNED", "extra": "→ users.id"},
        {"key": "FK", "name": "carrier_id", "type": "BIGINT UNSIGNED", "extra": "→ carriers.id"},
        {"key": "FK", "name": "origin_id", "type": "BIGINT UNSIGNED", "extra": "→ locations.id"},
        {"key": "FK", "name": "destination_id", "type": "BIGINT UNSIGNED", "extra": "→ locations.id"},
        {"key": "", "name": "cargo_type", "type": "VARCHAR(100)", "extra": "general/reefer/etc"},
        {"key": "", "name": "total_weight_kg", "type": "DECIMAL(10,2)", "extra": ">= 0"},
        {"key": "", "name": "total_volume_cbm", "type": "DECIMAL(10,2)", "extra": ">= 0"},
        {"key": "", "name": "expected_departure", "type": "DATETIME", "extra": ""},
        {"key": "", "name": "expected_arrival", "type": "DATETIME", "extra": "SLA Benchmark"},
        {"key": "", "name": "estimated_arrival", "type": "DATETIME", "extra": "Dynamic ETA"},
        {"key": "", "name": "current_status", "type": "VARCHAR(40)", "extra": "LIFECYCLE FLOW"},
        {"key": "", "name": "is_delayed", "type": "TINYINT(1)", "extra": "DEFAULT 0"},
        {"key": "", "name": "delay_hours", "type": "INT", "extra": "DEFAULT 0"},
        {"key": "", "name": "delay_severity", "type": "VARCHAR(20)", "extra": "LOW/MED/HIGH"},
        {"key": "", "name": "created_at", "type": "TIMESTAMP", "extra": ""},
        {"key": "", "name": "updated_at", "type": "TIMESTAMP", "extra": ""},
    ], header_color="#4F46E5", badge="CORE HUB")

    # 5. SHIPMENT_ITEMS (Child)
    t_items = draw_table(1.0, 6.8, 5.0, "shipment_items", [
        {"key": "PK", "name": "id", "type": "BIGINT", "extra": "UNSIGNED AI"},
        {"key": "FK", "name": "shipment_id", "type": "BIGINT UNSIGNED", "extra": "CASCADE DELETE"},
        {"key": "", "name": "item_name", "type": "VARCHAR(255)", "extra": ""},
        {"key": "", "name": "quantity", "type": "INT", "extra": "DEFAULT 1"},
        {"key": "", "name": "weight_kg", "type": "DECIMAL(10,2)", "extra": ""},
        {"key": "", "name": "volume_cbm", "type": "DECIMAL(10,2)", "extra": ""},
        {"key": "", "name": "package_type", "type": "VARCHAR(50)", "extra": "box/crate/pallet"},
        {"key": "", "name": "hs_code", "type": "VARCHAR(50)", "extra": "NULL"},
        {"key": "", "name": "created_at", "type": "TIMESTAMP", "extra": ""},
        {"key": "", "name": "updated_at", "type": "TIMESTAMP", "extra": ""},
    ], header_color="#6366F1", badge="DETAIL")

    # 6. TRACKING_EVENTS (Lifecycle History)
    t_events = draw_table(9.0, 8.8, 5.8, "tracking_events", [
        {"key": "PK", "name": "id", "type": "BIGINT", "extra": "UNSIGNED AI"},
        {"key": "FK", "name": "shipment_id", "type": "BIGINT UNSIGNED", "extra": "CASCADE DELETE"},
        {"key": "FK", "name": "location_id", "type": "BIGINT UNSIGNED", "extra": "NULLABLE"},
        {"key": "FK", "name": "created_by", "type": "BIGINT UNSIGNED", "extra": "→ users.id"},
        {"key": "", "name": "status_event", "type": "VARCHAR(50)", "extra": "Event Snapshot"},
        {"key": "", "name": "event_time", "type": "DATETIME", "extra": "Chronological"},
        {"key": "", "name": "location_name", "type": "VARCHAR(255)", "extra": ""},
        {"key": "", "name": "latitude", "type": "DECIMAL(10,7)", "extra": "Leaflet Pin"},
        {"key": "", "name": "longitude", "type": "DECIMAL(10,7)", "extra": "Leaflet Pin"},
        {"key": "", "name": "description", "type": "TEXT", "extra": ""},
        {"key": "", "name": "created_at", "type": "TIMESTAMP", "extra": ""},
        {"key": "", "name": "updated_at", "type": "TIMESTAMP", "extra": ""},
    ], header_color="#D97706", badge="HISTORICAL")

    # 7. SHIPMENT_EXCEPTIONS (Disruption / Issues)
    t_exceptions = draw_table(15.8, 8.8, 5.5, "shipment_exceptions", [
        {"key": "PK", "name": "id", "type": "BIGINT", "extra": "UNSIGNED AI"},
        {"key": "FK", "name": "shipment_id", "type": "BIGINT UNSIGNED", "extra": "CASCADE DELETE"},
        {"key": "FK", "name": "reported_by", "type": "BIGINT UNSIGNED", "extra": "→ users.id"},
        {"key": "", "name": "exception_type", "type": "VARCHAR(50)", "extra": "6 Defined Types"},
        {"key": "", "name": "severity", "type": "VARCHAR(20)", "extra": "LOW/MED/HIGH"},
        {"key": "", "name": "status", "type": "VARCHAR(30)", "extra": "OPEN/INVESTIGATING/RESOLVED"},
        {"key": "", "name": "detected_at", "type": "DATETIME", "extra": ""},
        {"key": "", "name": "resolved_at", "type": "DATETIME", "extra": "NULLABLE"},
        {"key": "", "name": "description", "type": "TEXT", "extra": ""},
        {"key": "", "name": "resolution_notes", "type": "TEXT", "extra": "NULLABLE"},
        {"key": "", "name": "created_at", "type": "TIMESTAMP", "extra": ""},
        {"key": "", "name": "updated_at", "type": "TIMESTAMP", "extra": ""},
    ], header_color="#DC2626", badge="DISRUPTIONS")

    # 8. NOTIFICATIONS (Customer Alerts)
    t_notifications = draw_table(21.8, 10.6, 5.2, "notifications", [
        {"key": "PK", "name": "id", "type": "BIGINT", "extra": "UNSIGNED AI"},
        {"key": "FK", "name": "user_id", "type": "BIGINT UNSIGNED", "extra": "CASCADE DELETE"},
        {"key": "FK", "name": "shipment_id", "type": "BIGINT UNSIGNED", "extra": "NULLABLE"},
        {"key": "", "name": "type", "type": "VARCHAR(50)", "extra": "STATUS/DELAY/ALERT"},
        {"key": "", "name": "title", "type": "VARCHAR(255)", "extra": ""},
        {"key": "", "name": "message", "type": "TEXT", "extra": ""},
        {"key": "", "name": "is_read", "type": "TINYINT(1)", "extra": "DEFAULT 0"},
        {"key": "", "name": "read_at", "type": "DATETIME", "extra": "NULLABLE"},
        {"key": "", "name": "created_at", "type": "TIMESTAMP", "extra": ""},
        {"key": "", "name": "updated_at", "type": "TIMESTAMP", "extra": ""},
    ], header_color="#059669", badge="COMMUNICATION")

    # 9. AUDIT_LOGS (Compliance & Security)
    t_audit = draw_table(21.8, 5.8, 5.2, "audit_logs", [
        {"key": "PK", "name": "id", "type": "BIGINT", "extra": "UNSIGNED AI"},
        {"key": "FK", "name": "user_id", "type": "BIGINT UNSIGNED", "extra": "NULLABLE"},
        {"key": "", "name": "action", "type": "VARCHAR(100)", "extra": "CREATE/UPDATE/DELETE"},
        {"key": "", "name": "entity_type", "type": "VARCHAR(100)", "extra": "App\\Models\\..."},
        {"key": "", "name": "entity_id", "type": "BIGINT UNSIGNED", "extra": ""},
        {"key": "", "name": "old_values", "type": "JSON", "extra": "NULLABLE"},
        {"key": "", "name": "new_values", "type": "JSON", "extra": "NULLABLE"},
        {"key": "", "name": "ip_address", "type": "VARCHAR(45)", "extra": "NULLABLE"},
        {"key": "", "name": "created_at", "type": "TIMESTAMP", "extra": "IMMUTABLE"},
    ], header_color="#64748B", badge="SECURITY AUDIT")

    # DRAW RELATIONSHIP CONNECTORS (CROW'S FOOT NOTATION & LABELS)
    def connect_tables(start_pt, end_pt, color="#64748B", label="", card_start="1", card_end="N", curve=0.0):
        # Draw curved or straight connection
        rad_str = f"arc3,rad={curve}" if curve != 0.0 else "arc3,rad=0"
        ax.annotate("", xy=end_pt, xytext=start_pt,
                    arrowprops=dict(arrowstyle="-|>", color=color, lw=1.6, connectionstyle=rad_str, alpha=0.85))
        # Place cardinality text
        mid_x = (start_pt[0] + end_pt[0]) / 2 + (curve * 1.5)
        mid_y = (start_pt[1] + end_pt[1]) / 2
        if label:
            ax.text(mid_x, mid_y + 0.15, label, color=color, fontsize=8, fontweight="bold",
                    ha="center", va="center", bbox=dict(boxstyle="round,pad=0.15", facecolor="#0B0F19", edgecolor=color, lw=0.8))
        ax.text(start_pt[0], start_pt[1] + 0.15, card_start, color="#FBBF24", fontsize=7.5, fontweight="bold", ha="center")
        ax.text(end_pt[0], end_pt[1] + 0.15, card_end, color="#38BDF8", fontsize=7.5, fontweight="bold", ha="center")

    # Carriers (1) -> Shipments (N)
    connect_tables((6.0, 14.5), (9.0, 14.5), color="#0D9488", label="carrier_id", card_start="1", card_end="N")

    # Locations (1) -> Shipments (N) (Origin & Destination)
    connect_tables((6.0, 10.5), (9.0, 13.0), color="#0D9488", label="origin/dest_id", card_start="1", card_end="N")

    # Users (1) -> Shipments (N) (Customer Owner)
    connect_tables((21.8, 14.0), (16.8, 14.0), color="#0284C7", label="customer_id", card_start="1", card_end="N")

    # Shipments (1) -> Shipment Items (N)
    connect_tables((9.0, 11.5), (6.0, 5.0), color="#6366F1", label="shipment_id", card_start="1", card_end="N", curve=-0.15)

    # Shipments (1) -> Tracking Events (N)
    connect_tables((11.5, 9.4), (11.5, 8.8), color="#D97706", label="shipment_id", card_start="1", card_end="N")

    # Shipments (1) -> Shipment Exceptions (N)
    connect_tables((16.0, 9.4), (17.5, 8.8), color="#DC2626", label="shipment_id", card_start="1", card_end="N")

    # Users (1) -> Notifications (N)
    connect_tables((24.4, 12.8), (24.4, 10.6), color="#059669", label="user_id", card_start="1", card_end="N")

    # Users (1) -> Audit Logs (N)
    connect_tables((24.4, 7.8), (24.4, 5.8), color="#64748B", label="user_id", card_start="1", card_end="N")

    # Users (1) -> Tracking Events (created_by)
    connect_tables((21.8, 13.2), (14.8, 6.5), color="#0284C7", label="created_by", card_start="1", card_end="N", curve=-0.2)

    # Shipments (1) -> Notifications (N)
    connect_tables((16.8, 11.5), (21.8, 9.0), color="#059669", label="shipment_id", card_start="1", card_end="N", curve=0.1)

    # Locations (1) -> Tracking Events (location_id)
    connect_tables((6.0, 8.5), (9.0, 7.0), color="#0D9488", label="location_id", card_start="1", card_end="N")

    # LEGEND & BUSINESS RULES BOX (Bottom Left)
    legend_box = patches.FancyBboxPatch((1.0, 0.6), 26.0, 2.0,
                                         boxstyle="round,pad=0.2",
                                         edgecolor="#334155", facecolor="#111827", linewidth=1.5)
    ax.add_patch(legend_box)
    
    ax.text(1.4, 2.2, "DIAGRAM LEGEND & BUSINESS RULES ENFORCEMENT", color="#38BDF8", fontsize=10.5, fontweight="bold")
    
    legend_texts = [
        "[PK] Primary Key (Auto-Increment BigInteger)",
        "[FK] Foreign Key (Referential Integrity Constraint)",
        "Cardinality: 1 (One / Parent) ➔ N (Many / Dependent Child)",
        "Status Flow: CREATED ➔ BOOKED ➔ PICKED_UP ➔ IN_TRANSIT ➔ DELIVERED",
        "Delay Rules: If ETA > Expected Arrival = DELAYED (Severity: LOW 0-6h, MED >6-24h, HIGH >24h)",
        "Exceptions: CUSTOMS_HOLD, WEATHER_DELAY, PORT_CONGESTION, DAMAGED, MISROUTED, DOCUMENTATION_ISSUE"
    ]
    
    col1 = "\n".join(legend_texts[:3])
    col2 = "\n".join(legend_texts[3:])
    
    ax.text(1.4, 1.4, col1, color="#E2E8F0", fontsize=8.5, linespacing=1.6)
    ax.text(14.0, 1.4, col2, color="#E2E8F0", fontsize=8.5, linespacing=1.6)

    plt.tight_layout()
    output_path = 'docs/diagrams/erd_diagram.jpg'
    fig.savefig(output_path, format='jpg', dpi=200, facecolor=fig.get_facecolor(), edgecolor='none')
    plt.close()
    print(f"ERD diagram generated: {output_path}")

create_erd_diagram()

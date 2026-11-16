import os
import matplotlib.pyplot as plt
import matplotlib.patches as patches
from PIL import Image

os.environ['MPLCONFIGDIR'] = os.path.abspath('docs/diagrams/.mpl_cache')
os.makedirs('docs/diagrams/.mpl_cache', exist_ok=True)

def create_use_case_diagram():
    fig, ax = plt.subplots(figsize=(24, 15), dpi=200)
    ax.set_xlim(0, 24)
    ax.set_ylim(0, 15)
    ax.axis('off')

    # Background
    fig.patch.set_facecolor('#0F172A') # Dark slate background
    ax.set_facecolor('#0F172A')

    # Title Banner
    title_box = patches.FancyBboxPatch((0.8, 13.6), 22.4, 1.1,
                                       boxstyle="round,pad=0.2",
                                       edgecolor="#38BDF8", facecolor="#1E293B", linewidth=2)
    ax.add_patch(title_box)
    ax.text(12, 14.25, "CARGOTRACE – UML USE CASE DIAGRAM",
            color="#F8FAFC", fontsize=20, fontweight="bold", ha="center", va="center")
    ax.text(12, 13.8, "Global Shipment Visibility & Tracking Platform | Project Specification v1.0 (UAS Web Programming 2)",
            color="#94A3B8", fontsize=11, ha="center", va="center")

    # System Boundary Box
    sys_box = patches.FancyBboxPatch((5.8, 0.6), 12.4, 12.6,
                                     boxstyle="round,pad=0.3",
                                     edgecolor="#64748B", facecolor="#1E293B", linewidth=2.5, linestyle="--")
    ax.add_patch(sys_box)
    ax.text(12, 12.85, "SYSTEM BOUNDARY : CargoTrace Application",
            color="#38BDF8", fontsize=13, fontweight="bold", ha="center", va="center")

    # Sub-domain Containers (Inside System)
    domains = [
        {"title": "AUTHENTICATION & ACCESS", "x": 6.2, "y": 9.9, "w": 5.6, "h": 2.5, "color": "#0284C7"},
        {"title": "MASTER DATA MANAGEMENT", "x": 6.2, "y": 6.9, "w": 5.6, "h": 2.6, "color": "#0D9488"},
        {"title": "SHIPMENT LIFECYCLE CORE", "x": 12.2, "y": 8.1, "w": 5.6, "h": 4.3, "color": "#4F46E5"},
        {"title": "TRACKING & TIMELINE VISIBILITY", "x": 6.2, "y": 3.7, "w": 5.6, "h": 2.8, "color": "#D97706"},
        {"title": "ETA, DELAY & EXCEPTIONS", "x": 12.2, "y": 3.7, "w": 5.6, "h": 4.0, "color": "#DC2626"},
        {"title": "ANALYTICS, DASHBOARD & AUDIT", "x": 6.2, "y": 1.0, "w": 11.6, "h": 2.3, "color": "#059669"},
    ]

    for d in domains:
        box = patches.FancyBboxPatch((d["x"], d["y"]), d["w"], d["h"],
                                     boxstyle="round,pad=0.15",
                                     edgecolor=d["color"], facecolor="#0F172A", linewidth=1.5, alpha=0.9)
        ax.add_patch(box)
        ax.text(d["x"] + d["w"]/2, d["y"] + d["h"] - 0.25, d["title"],
                color=d["color"], fontsize=9.5, fontweight="bold", ha="center", va="center")

    # Use Cases (Ovals)
    use_cases = {
        "uc_register": {"text": "Register Account\n(Self-service)", "x": 9.0, "y": 11.4, "w": 3.2, "h": 0.65},
        "uc_login": {"text": "Login & Token Auth\n(Web Session & Sanctum API)", "x": 9.0, "y": 10.5, "w": 3.4, "h": 0.65},
        
        "uc_carriers": {"text": "Manage Carriers\n(CRUD Logistics Operators)", "x": 9.0, "y": 8.6, "w": 3.2, "h": 0.65},
        "uc_locations": {"text": "Manage Locations\n(Ports, Hubs & Lat/Lng)", "x": 9.0, "y": 7.5, "w": 3.2, "h": 0.65},
        
        "uc_create_shipment": {"text": "Create Shipment & Items\n(Shipment Number & Cargo)", "x": 15.0, "y": 11.5, "w": 3.4, "h": 0.65},
        "uc_status_transition": {"text": "Update Status Lifecycle\n(CREATED→BOOKED→...→DELIVERED)", "x": 15.0, "y": 10.4, "w": 3.6, "h": 0.75},
        "uc_view_all_shipments": {"text": "View All Operational Shipments\n(Filter, Search & Server Pagination)", "x": 15.0, "y": 9.25, "w": 3.5, "h": 0.65},
        "uc_view_my_shipments": {"text": "View Owned Shipments\n(Customer RBAC Boundary)", "x": 15.0, "y": 8.35, "w": 3.2, "h": 0.6},
        
        "uc_tracking_events": {"text": "Add Tracking Event\n(Historical Checkpoint Data)", "x": 9.0, "y": 5.6, "w": 3.4, "h": 0.65},
        "uc_timeline": {"text": "View Shipment Timeline\n(Chronological Events)", "x": 9.0, "y": 4.5, "w": 3.2, "h": 0.65},
        
        "uc_map": {"text": "View Interactive Map Route\n(Leaflet.js + OpenStreetMap)", "x": 15.0, "y": 6.8, "w": 3.4, "h": 0.65},
        "uc_eta_delay": {"text": "Calculate ETA & Delay Severity\n(Deterministic Threshold Rule)", "x": 15.0, "y": 5.7, "w": 3.5, "h": 0.65},
        "uc_exceptions": {"text": "Manage & Resolve Exceptions\n(Customs, Weather, Damage)", "x": 15.0, "y": 4.5, "w": 3.4, "h": 0.65},
        
        "uc_dashboard": {"text": "View Live Operational Dashboard\n(Real DB Metrics & Chart.js)", "x": 9.2, "y": 2.1, "w": 3.6, "h": 0.65},
        "uc_audit_logs": {"text": "View System Audit Logs\n(Actor, Action & Timestamp)", "x": 14.8, "y": 2.1, "w": 3.3, "h": 0.65},
    }

    for key, uc in use_cases.items():
        oval = patches.FancyBboxPatch((uc["x"] - uc["w"]/2, uc["y"] - uc["h"]/2), uc["w"], uc["h"],
                                      boxstyle="round,pad=0.2",
                                      edgecolor="#38BDF8", facecolor="#334155", linewidth=1.5)
        ax.add_patch(oval)
        ax.text(uc["x"], uc["y"], uc["text"],
                color="#F8FAFC", fontsize=8, fontweight="600", ha="center", va="center")

    # Actors
    def draw_actor(x, y, name, role_desc, color="#38BDF8"):
        # Head
        circle = patches.Circle((x, y + 0.6), 0.28, edgecolor=color, facecolor="#1E293B", linewidth=2)
        ax.add_patch(circle)
        # Body
        ax.plot([x, x], [y + 0.32, y - 0.3], color=color, linewidth=2.5)
        # Arms
        ax.plot([x - 0.45, x + 0.45], [y + 0.1, y + 0.1], color=color, linewidth=2.5)
        # Legs
        ax.plot([x, x - 0.35], [y - 0.3, y - 0.8], color=color, linewidth=2.5)
        ax.plot([x, x + 0.35], [y - 0.3, y - 0.8], color=color, linewidth=2.5)
        # Label Box
        lbl_box = patches.FancyBboxPatch((x - 1.8, y - 1.5), 3.6, 0.65,
                                         boxstyle="round,pad=0.1",
                                         edgecolor=color, facecolor="#1E293B", linewidth=1.5)
        ax.add_patch(lbl_box)
        ax.text(x, y - 1.15, name, color=color, fontsize=10.5, fontweight="bold", ha="center", va="center")
        ax.text(x, y - 1.35, role_desc, color="#94A3B8", fontsize=7.5, ha="center", va="center")

    def draw_system_actor(x, y, name, desc, color="#F59E0B"):
        box = patches.FancyBboxPatch((x - 1.6, y - 0.7), 3.2, 1.4,
                                     boxstyle="round,pad=0.15",
                                     edgecolor=color, facecolor="#1E293B", linewidth=2)
        ax.add_patch(box)
        ax.text(x, y + 0.3, "«External System»", color=color, fontsize=8, ha="center", va="center")
        ax.text(x, y, name, color="#F8FAFC", fontsize=9.5, fontweight="bold", ha="center", va="center")
        ax.text(x, y - 0.35, desc, color="#CBD5E1", fontsize=7.5, ha="center", va="center")

    # Draw 3 Actors + 1 External
    draw_actor(2.6, 10.5, "Administrator", "Role: Full System Authority", color="#F43F5E")
    draw_actor(2.6, 5.5, "Logistics Operator", "Role: Shipment & Tracking Staff", color="#38BDF8")
    draw_actor(21.4, 9.0, "Customer", "Role: Shipment Consignee / Owner", color="#10B981")
    draw_system_actor(21.4, 4.5, "Geocoding & Weather", "OpenStreetMap / Open-Meteo", color="#F59E0B")

    # Connectors Admin
    admin_targets = ["uc_login", "uc_carriers", "uc_locations", "uc_view_all_shipments", "uc_dashboard", "uc_audit_logs"]
    for t in admin_targets:
        target = use_cases[t]
        ax.annotate("", xy=(target["x"] - target["w"]/2, target["y"]), xytext=(2.6, 10.5),
                    arrowprops=dict(arrowstyle="-", color="#F43F5E", lw=1.2, alpha=0.7))

    # Connectors Operator
    op_targets = ["uc_login", "uc_create_shipment", "uc_status_transition", "uc_view_all_shipments",
                  "uc_tracking_events", "uc_exceptions", "uc_eta_delay", "uc_dashboard"]
    for t in op_targets:
        target = use_cases[t]
        ax.annotate("", xy=(target["x"] - target["w"]/2, target["y"]), xytext=(2.6, 5.5),
                    arrowprops=dict(arrowstyle="-", color="#38BDF8", lw=1.2, alpha=0.7))

    # Connectors Customer
    cust_targets = ["uc_register", "uc_login", "uc_view_my_shipments", "uc_timeline", "uc_map"]
    for t in cust_targets:
        target = use_cases[t]
        ax.annotate("", xy=(target["x"] + target["w"]/2, target["y"]), xytext=(21.4, 9.0),
                    arrowprops=dict(arrowstyle="-", color="#10B981", lw=1.2, alpha=0.7))

    # Connectors External
    ext_targets = ["uc_map", "uc_exceptions"]
    for t in ext_targets:
        target = use_cases[t]
        ax.annotate("", xy=(target["x"] + target["w"]/2, target["y"]), xytext=(21.4, 4.5),
                    arrowprops=dict(arrowstyle="-", color="#F59E0B", lw=1.2, linestyle="--", alpha=0.8))

    # Include/Extend Relations (Dashed arrows with labels)
    # Timeline <<include>> Map
    ax.annotate("", xy=(use_cases["uc_map"]["x"], use_cases["uc_map"]["y"] - 0.35),
                xytext=(use_cases["uc_timeline"]["x"] + 1.2, use_cases["uc_timeline"]["y"] + 0.35),
                arrowprops=dict(arrowstyle="->", color="#A855F7", lw=1.5, linestyle=":", alpha=0.9))
    ax.text(12.0, 5.8, "«include»", color="#A855F7", fontsize=7.5, fontweight="bold", ha="center")

    # Status Transition <<include>> Add Tracking Event
    ax.annotate("", xy=(use_cases["uc_tracking_events"]["x"] + 1.2, use_cases["uc_tracking_events"]["y"] + 0.35),
                xytext=(use_cases["uc_status_transition"]["x"] - 1.2, use_cases["uc_status_transition"]["y"] - 0.35),
                arrowprops=dict(arrowstyle="->", color="#A855F7", lw=1.5, linestyle=":", alpha=0.9))
    ax.text(12.2, 7.8, "«include»", color="#A855F7", fontsize=7.5, fontweight="bold", ha="center")

    # Footer Notes
    footer = patches.FancyBboxPatch((0.8, 0.1), 22.4, 0.45,
                                    boxstyle="round,pad=0.1",
                                    edgecolor="#475569", facecolor="#1E293B", linewidth=1)
    ax.add_patch(footer)
    ax.text(12, 0.32, "Rules: 1. Strict Linear Lifecycle (CREATED→BOOKED→PICKED_UP→IN_TRANSIT→DELIVERED) | 2. Deterministic Delay Thresholds | 3. Server-side RBAC",
            color="#E2E8F0", fontsize=8, ha="center", va="center")

    plt.tight_layout()
    output_path = 'docs/diagrams/use_case_diagram.jpg'
    fig.savefig(output_path, format='jpg', dpi=200, facecolor=fig.get_facecolor(), edgecolor='none')
    plt.close()
    print(f"Use case diagram generated: {output_path}")

create_use_case_diagram()

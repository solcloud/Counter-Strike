import bpy
from os import path as p

output_path = p.join(p.expanduser("~/Desktop"), "csf-map-data.txt")

data = []
for category in (o for o in bpy.context.scene.objects if not o.parent): # bpy.context.selected_objects:
    data.append("G" + category.name)

    if (category.name == "Spawn"):
        for group in category.children:
            data.append("C" + group.name)
            for obj in group.children:
                data.append("A" + str(round(obj.location.x)) + "," + str(round(obj.location.y)) + "," + str(round(obj.location.z)))

    if (category.name == "Store" or category.name == "Plant"):
        data.append("C" + category.name)
        for obj in category.children:
            points = []
            for pv in obj.data.polygons[0].vertices:
                local = obj.data.vertices[pv].co
                world = obj.matrix_world @ local
                points.append(str(round(world.x)) + "," + str(round(world.y)) + "," + str(round(world.z)))
            data.append("B" + obj.name + "#" + '|'.join(points))

    if (category.name == "Map"):
        for group in category.children:
            data.append("C" + group.name)
            for obj in group.children:
                vert = obj.data.vertices
                data.append("O" + obj.name)  # todo use material check for isPenetrable
                for polygon in obj.data.polygons:
                    points = []
                    for pv in polygon.vertices:
                        local = vert[pv].co
                        world = obj.matrix_world @ local
                        points.append(str(round(world.x)) + "," + str(round(world.y)) + "," + str(round(world.z)))
                    data.append("P" + '|'.join(points))

f = open(output_path, "w")
f.write("\n".join(data))
f.close()

print(f"Map data exported to: {output_path}")

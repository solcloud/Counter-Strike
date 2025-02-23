import bpy
from os import path as p

output_path = p.join(p.expanduser("~/Desktop"), "csf-map-data.txt")

data = []
for category in (o for o in bpy.context.scene.objects if not o.parent):
    data.append("G" + category.name)
    if (category.name == "map"):
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
